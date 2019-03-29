<?php

namespace app\admin\controller;

use app\common\BaseHelper as Helper;
use app\common\Common;
use app\common\service\MiniProgramHelp;
use app\admin\model\Image;
use app\admin\model\Area;
use app\admin\model\Villages;

use think\Request;

/**
 * 会议
 */
class Meeting extends Base
{

    public function _initialize()
    {
        parent::_initialize();

        $defaultWhere = $this->getDefaultWhere();
        $this->defaultWhere = $defaultWhere;

        $this->searchFields = [
            'title' => [
                'label'     => '会议主题',
                'field'     => 'title',
                'type'      => 'text',
                'disabled'  => false,
                'condition' => 'like'

            ],
        ];
    }

    public function index(){
        $request = $this->request;
        if($request->isAjax()){
            $param = $request->param();
            $model = model($this->model);

            // 每页起始条数
            $start = $param['start'];
            // 每页显示条数
            $length = $param['length'];
            // 排序条件
            $columns = $param['order'][0]['column'];
            $order = $param['columns'][$columns]['data'].' '.$param['order'][0]['dir'];

            $where = $this->getFilterWhere($request);
            if($this->defaultWhere){
                //$model = $model->where($this->defaultWhere);
                $where = array_merge($where, $this->defaultWhere);
            }

            $list = $model->where($where)->limit($start, $length)->order($order)->select();
            $count = $model->where($where)->count();

            $result = [
                'status' => '1',
                'draw' => $param['draw'],
                'data' => $list,
                'recordsFiltered' => $count,
                'recordsTotal' => $count,
            ];

            return json($result);
        }

        return parent::index();
    }

    /**
     * Add
     */
    public function add(Request $request){

        return parent::add($request);
    }

    /**
     * Edit
     */
    public function edit(Request $request){
        $model = model($this->model);
        $id = $request->param('id');
        // Get data info
        $info = $model->find($id);

        if(!$info){
            return $this->error('error !', 'admin/' . strtolower($this->model) . '/index');
        }

        $this->assign('area', $this->getArea($info));

        $info->date = date('Y-m-d', strtotime($info->startTime));
        $info->startTime2 = substr($info->startTime, 11);
        $info->endTime2 = substr($info->endTime, 11);

        return $this->view->fetch($this->editView, [
            'info' => $info
        ]);
    }

    /**
     * Add/Edit Post
     */
    public function savePost(Request $request, $redirect = ''){
        $model = model($this->model);

        //save data
        if ($request->isAjax()) {
            $data = $request->param();

            $data['startTime'] = strtotime($data['date'] . ' ' . $data['startTime']);
            $data['endTime'] = strtotime($data['date'] . ' ' . $data['endTime']);

            if(isset($data['id']) && $data['id']){
                $data['updateDate'] = time();
                $data['updateOper'] = $this->admin->id;

                $result = $model->allowField(true)->save($data, ['id' => $data['id']]);

                if($result !== false) {
                    // Query执行后的操作
                    $model->_after_update($data);

                    // 写入日志
                    $logInfo = $this->admin->name . '更新了一条' . $this->model . '数据。';
                    Common::adminLog($request, $logInfo);

                    return json(['code' => 1, 'msg' => '保存成功！', 'id' => $data['id'], 'url' => url('admin/' . strtolower($this->model) . '/index')]);
                } else {
                    return json(['code' => 0, 'msg' => $model->getError()]);
                }
            }

            // Insert data
            $data['id'] = Helper::getUUID();
            $data['createDate'] = time();
            $data['createOper'] = $this->admin->id;
            // 生成会议签到二维码
            $data['signCode'] = $this->createMeetingCode($data['id'], $data['townId']);

            $result = $model->save($data);

            if($result !== false) {
                // Query执行后的操作
                $model->_after_insert($data);

                // 写入日志
                $logInfo = $this->admin->name . '添加了一条' . $this->model . '数据。';
                Common::adminLog($request, $logInfo);

                return json(['code' => 1, 'msg' => '添加成功！', 'id' => $data['id'], 'url' => url('admin/' . strtolower($this->model) . '/index')]);
            } else {
                return json(['code' => 0, 'msg' => $model->getError()]);
            }
        } else {
            return json(['code' => 0, 'msg' => '请求方式不正确！']);
        }
    }

    /**
     * 生成会议签到二维码
     *
     * @return bool|string
     */
    public function createMeetingCode($meetingId, $townId){
        $businessId = db('townprogram')->where(['townId' => $townId])->value('id');
        // 接口调用凭据
        $access_token = MiniProgramHelp::getAuthorizerToken($businessId);
        //echo $access_token .'<br />';
        $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $access_token;
        $postData["scene"] = $meetingId;
        $postData["path"] = "pages/meeting/signin";
        //$postData["page"] = "pages/meeting/signin";

        $result = Helper::curlPost($url, json_encode($postData));
        if(json_decode($result,true)['errcode']){
            return false;
        }
        $upload = config('path.Upload_path');
        $savepath = 'meeting/' . date('Ymd');
        $filepath = $upload . $savepath;
        if(!file_exists($filepath)){
            mkdir($filepath, 0777, true);
        }
        $filename = Helper::getUUID() . '.png';

        file_put_contents($filepath . '/' . $filename, $result);

        return $savepath . '/' . $filename;
    }

    /**
     * 详情
     */
    public function detail(Request $request){
        $model = model($this->model);
        $id = $request->param('id');
        // Get data info
        $info = $model->find($id);

        if(!$info){
            return $this->error('error !', 'admin/' . strtolower($this->model) . '/index');
        }

        $info->organization = $this->getOrganization($info->createOper);

        // 参会人员
        $joinmeetingB = db('joinmeeting');
        $join = [
            ['__MEMBER__ m', 'a.memberId = m.id'],
            //['__ORGANIZATION__ o', 'm.organizationId = o.id'],
        ];
        $field = 'm.avatar, m.name, m.job';
        $info->joinmeeting = $joinmeetingB->alias('a')->where(['meetingId' => $id])->join($join)->field($field)->order('a.createDate ASC')->select();

        return $this->view->fetch('detail', [
            'info' => $info
        ]);
    }

    /**
     * Delete
     */
    public function delete(){
        $model = model($this->model);

        $request = $this->request;
        $id = $request->param('id');
        if($id){
            $result = $model->where('id', $id)->delete();

            if($result !== false){
                // Query执行后的操作
                $model->_after_delete($id);

                $logInfo = $this->admin->name . '删除了1条' . $this->model . '数据。';
                Common::adminLog($request, $logInfo);

                return $this->success('删除成功！', 'admin/' . strtolower($this->model) . '/index');
            }else{
                return $this->error('删除失败！', 'admin/' . strtolower($this->model) . '/index');
            }
        }
    }

    /**
     * 筛选条件
     */
    public function getFilterWhere($request){
        $param = $request->param();
        $where = [];
        if($param['search']['value']) {
            $filter = json_decode($param['search']['value'],true);
            if(isset($filter['cityId']) && $filter['cityId']){
                $where['cityId'] = $filter['cityId'];
            }
            if(isset($filter['xianId']) && $filter['xianId']){
                $where['xianId'] = $filter['xianId'];
            }
            if(isset($filter['townId']) && $filter['townId']){
                $where['townId'] = $filter['townId'];
            }
            if(isset($filter['villageId']) && $filter['villageId']){
                $where['villageId'] = $filter['villageId'];
            }

            if(isset($filter['title']) && $filter['title']){
                $where['title'] = ['like', '%'.$filter['title'].'%'];
            }
        }

        return $where;
    }

    /**
     * 成员列表
     * @return \think\response\Json
     */
    public function memberlist(){
        $request = $this->request;
        $param = $request->param();
        $model = model('Member');

        // 每页起始条数
        $start = $param['start'];
        // 每页显示条数
        $length = $param['length'];

        $where = [
            'isParty' => 1
        ];
        if($param['search']['value']) {
            $filter = json_decode($param['search']['value'],true);
            if(isset($filter['name']) && $filter['name']){
                $where['name'] = ['like', '%'.$filter['name'].'%'];
            }
        }

        $list = $model->alias('a')->where($where)->limit($start, $length)->select();
        $count = $model->alias('a')->where($where)->count();

        $result = [
            'status' => '1',
            'draw' => $param['draw'],
            'data' => $list,
            'recordsFiltered' => $count,
            'recordsTotal' => $count,
        ];

        return json($result);
    }

    /**
     * 参加会议人员 列表
     * @return \think\response\Json
     */
    public function joinmeetingList(){
        $request = $this->request;
        $param = $request->param();
        $model = model('Joinmeeting');

        // 每页起始条数
        $start = $param['start'];
        // 每页显示条数
        $length = $param['length'];

        if(empty($param['id'])){
            $result = [
                'status' => '0',
                'draw' => $param['draw'],
                'data' => [],
                'recordsFiltered' => 0,
                'recordsTotal' => 0,
            ];
            return json($result);
        }
        $where = [
            'meetingId' => $param['id']
        ];
        if(isset($param['isSign']) && $param['isSign']){
            $where['isSign'] = $param['isSign'];
        }

        $join = [
            ['__MEMBER__ m', 'a.memberId = m.id'],
            ['__ORGANIZATION__ o', 'm.organizationId = o.id'],
        ];
        $field = 'a.*, m.avatar, m.name, m.job, o.name as organizationName';
        $list = $model->alias('a')->where($where)->join($join)->field($field)->limit($start, $length)->select();
        $count = $model->alias('a')->where($where)->join($join)->count();

        $result = [
            'status' => '1',
            'draw' => $param['draw'],
            'data' => $list,
            'recordsFiltered' => $count,
            'recordsTotal' => $count,
        ];

        return json($result);
    }

    /**
     * 添加参会人员
     */
    public function addJoinmeeting(Request $request){
        $model = model('Joinmeeting');

        //save data
        if ($request->isAjax()) {
            $params = $this->request->param();

            if(!$params['meetingId']){
                return json(['code' => 0, 'msg' => '添加失败！']);
            }

            //$ids = $params['id'];
            $ids = explode(',', $params['ids']);
            $data = [];
            foreach ($ids as $key => $id) {
                $data['meetingId'] = $params['meetingId'];
                $data['memberId'] = $id;
                // 是否已参加
                $isJoin = $model->where($data)->find('memberId');
                if($isJoin){
                    continue;
                }
                $data['id'] = Helper::getUUID();
                $data['createDate'] = time();
                $data['isSign'] = 2;

                $result = $model->insert($data);
            }

            if($result !== false) {
                // Query执行后的操作
                $model->_after_update($data);

                // 写入日志
                $logInfo = $this->admin->name . '添加了参会人员数据。';
                Common::adminLog($request, $logInfo);

                return json(['code' => 1, 'msg' => '添加成功！', 'id' => $data['id']]);
            } else {
                return json(['code' => 0, 'msg' => $model->getError()]);
            }
        } else {
            return json(['code' => 0, 'msg' => '请求方式不正确！']);
        }
    }

    /**
     * 删除参会人员
     */
    public function delJoinmeeting(){
        $model = model('Joinmeeting');

        $request = $this->request;
        $id = $request->param('id');

        // 删除一条
        $info = $model->find(['id', $id]);
        if(!$info){
            return json(['code' => 0, 'msg' => 'error']);
        }
        $result = $model->where('id', $id)->delete();

        if($result !== false){
            // Query执行后的操作
            $model->_after_delete($id);

            $logInfo = $this->admin->name . '删除了1条参会人员数据。';
            Common::adminLog($request, $logInfo);
        }

        if($result !== false){
            return json(['code' => 1, 'msg' => '删除成功！']);
        }else{
            return json(['code' => 0, 'msg' => '删除失败']);
        }
    }

}