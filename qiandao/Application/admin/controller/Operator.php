<?php

namespace app\admin\controller;

use app\common\BaseHelper as Helper;
use app\common\Common;
use app\admin\model\Member;
use app\admin\model\Organization;
use app\admin\model\Organizationjob;
use app\admin\model\Operatorjob;
use app\admin\model\Menu;
use app\admin\model\Area;

use think\Request;

class Operator extends Base
{

    protected $organizationId;

    protected $organization;

    protected $jobId;

    protected $job;

    public function _initialize()
    {
        parent::_initialize();

        switch ($this->admin->type) {
            case 0:
                $this->defaultWhere = ['a.isDelete' => ['neq', 1],'a.id' => ['neq',session('ADMIN')['id']]];
                break;
            case 1:
                $this->defaultWhere = ['a.isDelete' => ['neq', 1],'a.type' => ['neq', 0],'a.id' => ['neq',session('ADMIN')['id']]];
                break;            
            default:
                $this->defaultWhere = ['a.isDelete' => ['neq', 1],'a.id' => ['neq',session('ADMIN')['id']]];
                break;
        }
    }

    public function index(){
        $request = $this->request;
        $param = $request->param();
        if ($request->param('reset')) {
            return redirect(fullUrl($request->path()));
        }
        if($request->isAjax()){
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
                $where = array_merge($this->defaultWhere, $where);
            }

            $list = $model->alias('a')->where($where)->limit($start, $length)->order($order)->select();
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
        return $this->view->fetch($this->indexView, [
            'pageSize' => ['url' => fullUrl($request->path())],
            'searchFields' => $this->searchFields,
            'param' => $request->param()
        ]);
    }

    /**
     * Add
     */
    public function add(Request $request){
        $menus = Menu::field('id,name,parentId')->where(['status'=>1,'isRole'=>1])->select();
        $menuMap = [];
        foreach ($menus as $k => $v) {
            $menuMap[$v['parentId']][] = $v->toArray();
        }
        // dump($menuMap);die;
        $this->assign('menu',$menuMap);

        return parent::add($request);
    }

    /**
     * Add Post
     */
    public function addPost(Request $request, $redirect = ''){
        $model = model('Operator');
        $param = $this->request->param();
        if(!($this->checkLoginName($param['loginName']))) {
            return $this->error('登录用户名重复，请重新填写');
        }
        $menuIds = (array)$param['menuId'];
        foreach ($menuIds as $v) {
            $find = Menu::where('id',$v)->find();
            if($find['parentId'] != 0) {
                $menuIds[] = $find['parentId'];
            }
        }
        $menuIds[] = 1;
        $data = [
            'id' => Helper::getUUID(),
            'createDate' => time(),
            'loginName' => $param['loginName'],
            // 'townPid' => $param['townId'],  //根据所选乡镇查找townprogram表主键id
            'type' => $param['level'],
            'createOper' => $this->admin->id
        ];
        $data['password'] = Helper::passwordEncrypt($param['pass_one']);
        if($model->insert($data)) {
            return $this->success('添加成功！', 'admin/' . strtolower($this->model) . '/index');
        }
        return $this->error('添加失败！',url('admin/operator/index'));
    }

    public function edit(Request $request) {
        $areaMdl = new Area();
        $city = Helper::makeOptions(
            $areaMdl,
            ['level' => 1],
            ['id' => 'asc']
        );

        $menus = Menu::field('id,name,parentId')->where(['status'=>1,'isRole'=>1])->select();
        $menuMap = [];
        foreach ($menus as $k => $v) {
            $menuMap[$v['parentId']][] = $v->toArray();
        }

        $this->assign('city',$city);
        $this->assign('menu',$menuMap);
        return parent::edit($request);
    }

    public function editPost(Request $request, $redirect = '') {
        $model = model('Operator');
        $param = $this->request->param();
        if($param['loginName'] != $param['oldLoginName']) {
            if(!($this->checkLoginName($param['loginName']))) {
                return $this->error('登录用户名重复，请重新填写');
            }
        }
        $menuIds = (array)$param['menuId'];
        foreach ($menuIds as $v) {
            $find = Menu::where('id',$v)->find();
            if($find['parentId'] != 0) {
                $menuIds[] = $find['parentId'];
            }
        }
        $menuIds[] = 1;
        $data = [
            'loginName' => $param['loginName'],
            'memberId' => $param['memberId'],
            'menuId' => implode(',', array_unique($menuIds)),
            'updateDate' => time(),
            'type' => $param['level'],
            'cityId' => $param['cityId'],
            'xianId' => $param['xianId'],
            'townId' => $param['townId'],
            'villageId' => $param['villageId'],
            'updateOper' => $this->admin->id,
        ];
        if(isset($param['pass_one']) && !empty($param['pass_one'])) {
            $data['password'] = Helper::passwordEncrypt($param['pass_one']);
        }
        $save = $model->where(['id'=>$param['id']])->update($data);
        if($save) {
            return $this->success('保存成功！', 'admin/' . strtolower($this->model) . '/index');
        } else{
            return $this->error('保存失败！',url('admin/' . strtolower($this->model) . '/index'));
        }
    }

    public function checkLoginName($loginName)
    {
        $model = model('Operator');

        $result = $model->where(['loginName' => $loginName, 'isDelete' => 2])->field('loginName')->find();

        if($result){
            return false;
            return json(['status' => 1]);
        }else{
            return true;
            return json(['status' => 0]);
        }
    }

    public function delete()
    {
        $model = model($this->model);

        $request = $this->request;
        $id = $request->param('id');

        $info = $model->find(['id', $id]);
        if(!$info){
            return redirect('admin/'.$this->model.'/index');
        }
        $result = $model->where('id', $id)->update(['isDelete' => 1]);
        if($result !== false) {

            $logInfo = $this->admin->name . '删除了1条' . $this->model . '数据。';
            Common::adminLog($request, $logInfo);
            return $this->success('删除成功！', url('admin/' . strtolower($this->model) . '/index'));
        }
        return $this->error('删除失败！', url('admin/' . strtolower($this->model) . '/index'));
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
                $where['a.cityId'] = $filter['cityId'];
            }
            if(isset($filter['xianId']) && $filter['xianId']){
                $where['a.xianId'] = $filter['xianId'];
            }
            if(isset($filter['townId']) && $filter['townId']){
                $where['a.townId'] = $filter['townId'];
            }
            if(isset($filter['villageId']) && $filter['villageId']){
                $where['a.villageId'] = $filter['villageId'];
            }
        }

        return $where;
    }

}