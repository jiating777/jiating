<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/2 0002
 * Time: 上午 11:26
 */
namespace app\admin\controller;

use app\common\BaseHelper;
use app\common\model\AppManage;
use app\common\model\Submissionaudit;
use app\common\validate\WechatAuth;
use app\lib\exception\ParameterException;
use think\Db;
use think\Exception;
use think\Request;

class MiniProgram extends Base{
    public function index(){

        $businessId = session('ADMIN')['townPid'];
        //获取小程序信息
        $data['businessMiniProgram'] = \app\admin\model\Townprogram::get($businessId);

        $AppManage = AppManage::where('projectId',$businessId)->find();

        //获取授权方的帐号基本信息
        //$data['authorizerInfo'] = MiniProgramHelp::getAuthorizerInfo($businessId);
        $data['authorizerInfo'] = json_decode(BaseHelper::curlGet(config('miniprogram.wx_domain').'/api/WechatOpenApi/getAuthorizerInfo?b=' . $businessId),true);
        //dump($data['authorizerInfo']);exit;
        if (!empty($data['authorizerInfo']['authorization_info'])) {
            //更新小程序二维码
            if ($data['authorizerInfo']['authorizer_info']['qrcode_url']) {
                $data['businessMiniProgram']->qrcodeUrl = $data['authorizerInfo']['authorizer_info']['qrcode_url'];
                $data['businessMiniProgram']->save();
            }

            if(!$AppManage->appName){
                $AppManage->appName = $data['authorizerInfo']['authorizer_info']['nick_name'];
            }

            //更新authorizerRefreshToken、authorizerAccessToken、authorizerAccessTokenExpires
            if ($data['authorizerInfo']['authorization_info']['authorizer_refresh_token'] != $AppManage->authorizerRefreshToken) {
                $AppManage->authorizerRefreshToken = $data['authorizerInfo']['authorization_info']['authorizer_refresh_token'];
                if ($AppManage->save()) {
                    BaseHelper::curlGet(config('miniprogram.wx_domain').'/api/WechatOpenApi/getAuthorizerToken?b=' . $businessId . '&isRefresh=true');
//                    MiniProgramHelp::getAuthorizerToken($businessId, true);
                }
            }
        }

        //查询小程序模板
//        if (!empty($data['businessMiniProgram']->templateId)) {
//            $MiniprogramtemplateModel = new Miniprogramtemplate();
//            $data['template'] = $MiniprogramtemplateModel->where(['id' => $data['businessMiniProgram']->templateId, 'status' => 10])->find();
//        }

        //小程序审核版本的审核状态
        if (!empty($AppManage) && $AppManage->wxStatus == 2) {
            //$businessMiniProgramStatus = MiniProgramHelp::getAuditStatus($businessId);
            $businessMiniProgramStatus = json_decode(BaseHelper::curlGet(config('miniprogram.wx_domain').'/api/WechatOpenApi/getAuditStatus?b=' . $businessId),true);

            //修改状态
            AppManage::updateStatusByAuditStatus($businessMiniProgramStatus, $AppManage);

        }
        $qrcode = $this->showqrcode();
        if(isset($data['authorizerInfo']['errcode']) && isset($data['authorizerInfo']['errmsg'])){
            $data['authorizerInfo']['authorizer_info']['nick_name'] = '';
            $data['authorizerInfo']['authorizer_info']['principal_name'] = '';
        }
        $Submissionauditmodel = new Submissionaudit();
        $submissionaudit = $Submissionauditmodel->where('projectId',$businessId)->whereTime('create_time','w')->select();
        $tijiaonums = count($submissionaudit);
        $this->assign('qrcode',$qrcode);
        $this->assign('data',$data);
        $this->assign('tenantId',$businessId);
        $this->assign('appManage',$AppManage);
        $this->assign('tijiaonums',$tijiaonums);
        $this->assign('shengyunums',(config('miniprogram.SubmissionNums') - $tijiaonums));
        return $this->fetch();
    }







    /**
     * 跳转二维码授权页面
     * @author fei <xliang.fei@gmail.com>
     */
    public function AuthCode()
    {
        $this->redirect(config('miniprogram.wx_domain').'/admin/MiniProgram/authindex?b=' . session('ADMIN')['townPid'].'&type=1');

    }

    /**
     * 添加小程序体验者
     * @param Request $request
     * @return \think\response\Json
     */
    public function addexperiencer(Request $request){

        $wechat_id = $request->param()['wechat_id'];

        $res = json_decode(BaseHelper::curlGet(config('miniprogram.wx_domain').'/api/WechatOpenApi/addexperiencer?b=' . session('ADMIN')['townPid'] . '&wechatid=' . $wechat_id),true);
        if($res['errcode'] != 0){
            if($res['errcode'] == 85001){
                return show(config('status.ERROR_STATUS'),'微信号不存在或微信号设置为不可搜索','微信号不存在或微信号设置为不可搜索');
            }elseif ($res['errcode'] == 85002){
                return show(config('status.ERROR_STATUS'),'小程序绑定的体验者数量达到上限','小程序绑定的体验者数量达到上限');
            }elseif ($res['errcode'] == 85003){
                return show(config('status.ERROR_STATUS'),'微信号绑定的小程序体验者达到上限','微信号绑定的小程序体验者达到上限');
            }elseif ($res['errcode'] == 85004){
                return show(config('status.ERROR_STATUS'),'微信号已经绑定','微信号已经绑定');
            }else{
                return show(config('status.ERROR_STATUS'),'系统繁忙','系统繁忙');
            }
        }else{
            return show(config('status.SUCCESS_STATUS'),'绑定成功','绑定成功');
        }

    }

    /**
     * 单个商户提交审核
     * @return mixed|\think\response\Json
     */
    public function onedemosubmissionaudit()
    {


        if(request()->isPost()){
            $businessId = session('ADMIN')['townPid'];
            //检查该商户提交的次数
            if(!$this->checkSubmissionNums($businessId)){
                return show(config('status.ERROR_STATUS'),'提交审核次数超过限制','提交审核次数超过限制');
            }
            $templateId = $this->newtemplateId();
            $postdata['template_id'] = $templateId['template_id'];
            $postdata['user_version'] = $templateId['user_version'];
            $postdata['user_desc'] = $templateId['user_desc'];
            $postdata['projectType'] = 1;
            $postdata['tag'] = $templateId['user_desc'];
            $validate = new WechatAuth();
            if(!$validate->check($postdata)){
                return show(config('status.ERROR_STATUS'),$validate->getError(),$postdata);
            }

            $Townprogram = \app\admin\model\Townprogram::get($businessId);
            if(!$Townprogram){
                return show(config('status.ERROR_STATUS'),'该商户不存在','该商户不存在');
            }
            $AppManage = AppManage::where('projectId',$Townprogram->id)->find();

            //修改服务器地址
            //$result = MiniProgramHelp::modifyDomain(session('TENANT_ID'));
            $result = json_decode(BaseHelper::curlGet(config('miniprogram.wx_domain').'/api/WechatOpenApi/modifyDomain?b=' . $businessId),true);
            if (isset($result['errcode']) && in_array($result['errcode'], [0, 85017])) {

                //提交代码
                //$resultCommit = MiniProgramHelp::commitCode($businessId,$postdata['template_id'],$postdata['user_version'],$postdata['user_desc']);
                $url = config('miniprogram.wx_domain').'/api/WechatOpenApi/commitCode?b=' . $businessId . '&template_id=' . $postdata['template_id'] . '&user_version=' . $postdata['user_version'] . '&user_desc=' . $postdata['user_desc'];
                $resultCommit = json_decode(BaseHelper::curlGet($url),true);
                if ($resultCommit['errcode'] == 0) {
                    //提交审核
                    //$resultSubmit = MiniProgramHelp::submitAuditCode($businessId,$postdata);
                    $resultSubmit = json_decode(BaseHelper::curlGet(config('miniprogram.wx_domain').'/api/WechatOpenApi/submitAuditCode?b=' . $businessId . '&posttag=' . $postdata['tag']),true);

                    if (!empty($resultSubmit['errmsg']) && $resultSubmit['errmsg'] == 'ok') {
//                        $templateModel = new Miniprogramtemplate();
//                        $templateModel->businessId = $businessId;
//                        $templateModel->templateId = $postdata['template_id'];
//                        $templateModel->user_version = $postdata['user_version'];
//                        $templateModel->user_desc = $postdata['user_desc'];
//                        $templateModel->tag = $postdata['tag'];
//                        $templateModel->save();

                        $AppManage->auditid = $resultSubmit['auditid'];
                        $AppManage->version = $resultCommit['versions'];
                        $AppManage->templateId = $resultCommit['templateId'];
                        $AppManage->wxStatus = 2;
                        $AppManage->save();
                        $data = [
                            'projectId' => $businessId,
                            'create_time' => date('Y-m-d H:i:s',time())
                        ];
                        $Submissionaudit = new Submissionaudit();
                        $Submissionaudit->data($data);
                        $Submissionaudit->save();

                        return show(config('status.SUCCESS_STATUS'),"提交审核成功",$postdata);
                    } else {

                        if (!empty($resultSubmit['errcode']) && $resultSubmit['errcode'] == '85009') {
                            return show(config('status.ERROR_STATUS'),"提交审核失败,已经有正在审核的版本!",$resultSubmit['errmsg']);
                        }elseif ($resultSubmit['errcode'] == '85006'){
                            return show(config('status.ERROR_STATUS'),"小程序标签格式错误!",$resultSubmit['errmsg']);
                        }
                        return show($resultSubmit['errcode'],"提交审核失败01",$resultSubmit['errmsg']);
                    }
                } elseif ($resultCommit['errcode'] == 85014 || $resultCommit['errcode'] == 85043) {
                    return show(config('status.ERROR_STATUS'),"模板错误",$resultCommit['errmsg']);
                }

            } else {
                return show(config('status.ERROR_STATUS'),"提交审核失败02",'');
            }

        }else{
            return $this->fetch();
        }


        //return json($data);
    }

    private function checkSubmissionNums($businessId){
        $Submissionaudit = new Submissionaudit();
        $submissionaudit = $Submissionaudit->where('projectId',$businessId)->where('create_time','w')->select();
        if(count($submissionaudit) < config('miniprogram.SubmissionNums')){
            return true;
        }
        return false;
    }

    /**
     * 获取最新的模板
     * @return \think\response\Json
     */
    public function newtemplateId(){
        $templatelist = json_decode(BaseHelper::curlGet(config('miniprogram.wx_domain').'/api/WechatOpenApi/getTemplateList'),true);
        if($templatelist['errcode'] == 0){
//            return show(config('status.SUCCESS_STATUS'),'ok',array_reverse($templatelist['template_list'])[0]);
            return array_reverse($templatelist['template_list'])[0];
        }elseif ($templatelist['errcode'] == 85064){
            return show(config('status.ERROR_STATUS'),'找不到模版',$templatelist['template_list']);
        }else{
            return show(config('status.ERROR_STATUS'),'系统繁忙',$templatelist['template_list']);
        }
    }

    /**
     * 获取小程序体验二维码
     * @return \think\response\Json
     */
    public function showqrcode(){
        $businessId = session('ADMIN')['townPid'];
        //$qrcodeurl = MiniProgramHelp::getQrcode($businessId);
        $qrcodeurl = json_decode(BaseHelper::curlGet(config('miniprogram.wx_domain').'/api/WechatOpenApi/showqrcode?b=' . $businessId),true);
        if($qrcodeurl){

            return $qrcodeurl;
        }else{
            return '';
        }

    }

    /**
     * 授权解绑
     * @return \think\response\Json
     */
    public function wechatjiebang(){
        $businessId = session('ADMIN')['townPid'];
        Db::startTrans();
        try{
            $Townprogram = \app\admin\model\Townprogram::get($businessId);
            $AppManage = AppManage::where('projectId',$Townprogram->id)->find();
            if($AppManage->wxStatus != 0){
                $AppManage->wxStatus = 0;
                $AppManage->auditid = 0;
                $AppManage->appId = null;
                $AppManage->authorizerAccessToken = null;
                $AppManage->authorizerRefreshToken = null;
                $AppManage->authTime = null;
                $AppManage->auditMsg = null;
                if($AppManage->save()){
                    $Townprogram->appId = null;
                    $Townprogram->save();
                }
                Db::commit();
                return show(config('status.SUCCESS_STATUS'),"解绑成功",'解绑成功');
            }else{
                throw new ParameterException([
                    'msg' => '当前无法解绑'
                ]);
            }
        }catch (Exception $ex){
            Db::rollback();
            return show(config('status.ERROR_STATUS'),$ex->getMessage(),'当前无法解绑');
        }

    }


}