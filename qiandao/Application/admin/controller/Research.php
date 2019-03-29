<?php

namespace app\admin\controller;

use app\common\BaseHelper as Helper;
use app\common\Common;
use app\admin\model\Image;
use app\admin\model\Area;
use app\admin\model\Villages;

use think\Request;

/**
 * 投票调研
 */
class Research extends Base
{

    public function _initialize()
    {
        parent::_initialize();

        $defaultWhere = $this->getDefaultWhere();
        $this->defaultWhere = $defaultWhere;
        $this->searchFields = [
            'name' => [
                'label'     => '调研名称',
                'field'     => 'name',
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

            $data['endTime'] = strtotime($data['endTime']);

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

            $result = $model->save($data);

            if($result !== false) {
                // 单图
                if(isset($data['imgId']) && !empty($data['imgId'])){
                    Image::where('id', $data['imgId'])->update(['relatedId' => $data['id'], 'relatedTable' => 'Research']);
                }

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

        // 调研项目
        $examDB = db('researchproject');
        $info->researchproject = $examDB->where(['researchId' => $id])->order('sorting ASC')->select();

        return $this->view->fetch('detail', [
            'info' => $info
        ]);
    }

    /**
     * 参加调研 列表
     * @return \think\response\Json
     */
    public function joinresearchList(){
        $request = $this->request;
        $param = $request->param();
        $model = model('Joinresearch');

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
            'researchId' => $param['id']
        ];

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
     * 查看调研人及调研结果
     */
    public function viewJoinresearch(Request $request){
        $model = model('Joinresearch');
        $id = $this->request->param('id');
        $info = $model->where(['id' => $id])->find();
        if(!$info){
            return json(['code' => 0, 'msg' => 'error']);
        }

        // 调研结果
        $researchresultsDB = db('researchresults');

        $join = [
            ['__RESEARCHPROJECT__ r', 'a.projectId = r.id'],
        ];
        $field = 'a.*, r.*';
        $info->researchresults = $researchresultsDB->alias('a')->where(['a.joinId' => $id])->join($join)->field($field)->order('r.sorting ASC')->select();

        $this->assign('info', $info);

        return json(['code' => 1, 'data' => $this->fetch('research/viewresearchresults')]);
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

            if(isset($filter['name']) && $filter['name']){
                $where['name'] = ['like', '%'.$filter['name'].'%'];
            }
        }

        return $where;
    }

    /**
     * 调研项目
     */
    public function researchproject(Request $request){
        $request = $this->request;
        if($request->isAjax()){
            $param = $request->param();
            $model = model('Researchproject');

            $id = $request->param('id');
            if(!$id){
                $result = [
                    'status' => '1',
                    'draw' => $param['draw'],
                    'data' => [],
                    'recordsFiltered' => 0,
                    'recordsTotal' => 0,
                ];

                return json($result);
            }

            // 每页起始条数
            $start = $param['start'];
            // 每页显示条数
            $length = $param['length'];
            // 排序条件
            $columns = $param['order'][0]['column'];
            $order = $param['columns'][$columns]['data'].' '.$param['order'][0]['dir'];

            $where = [
                'researchId' => $id
            ];

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
    }

    /**
     * 查看项目 添加/编辑
     */
    public function viewProject(Request $request){
        $model = model('Researchproject');
        $info = [];
        $id = $this->request->param('id');
        if($id){
            $info = $model->where(['id' => $id])->find();
        }

        $this->assign('info', $info);

        return json(['code' => 1, 'data' => $this->fetch('research/viewproject')]);
    }

    /**
     * 保存项目 添加/编辑 提交
     */
    public function saveProject(Request $request){
        $model = model('Researchproject');

        //save data
        if ($request->isAjax()) {
            $data = $request->param();
            $data = $this->handleData($data);

            if(!$data['researchId']){
                return json(['code' => 0, 'msg' => '保存失败！']);
            }
            if(isset($data['id']) && $data['id']){
                $data['updateDate'] = time();
                $data['updateOper'] = $this->admin->id;

                $result = $model->allowField(true)->save($data, ['id' => $data['id']]);

                if($result !== false) {
                    // Query执行后的操作
                    $model->_after_update($data);

                    // 写入日志
                    $logInfo = $this->admin->name . '更新了一条调研项目数据。';
                    Common::adminLog($request, $logInfo);

                    return json(['code' => 1, 'msg' => '保存成功！', 'id' => $data['id']]);
                } else {
                    return json(['code' => 0, 'msg' => $model->getError()]);
                }
            }

            // Insert data
            $data['id'] = Helper::getUUID();
            $data['createDate'] = time();
            $data['createOper'] = $this->admin->id;

            $result = $model->save($data);

            if($result !== false) {
                // Query执行后的操作
                $model->_after_update($data);

                // 写入日志
                $logInfo = $this->admin->name . '添加了一条调研项目数据。';
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
     * 处理 Data 数据
     *
     * @param $data
     * @return mixed
     */
    public function handleData($data){
        $options = [];
        $value = [];
        switch($data['type']){
            case 1 :
                $NO = $data['option_NO1'];
                $value = $data['option_value1'];
                break;
            case 2 :
                $NO = $data['option_NO2'];
                $value = $data['option_value2'];
                break;
            default : break;
        }
        foreach ($value as $key => $val) {
            if(!trim($val)){
                continue;
            }
            $options[] = [
                'option_NO' => $NO[$key],
                'option_value' => $val,
            ];
        }
        $data['option'] = json_encode($options);

        return $data;
    }

    /**
     * 删除调研项目
     */
    public function delProject(){
        $model = model('Researchproject');

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

            $logInfo = $this->admin->name . '删除了1条调研项目数据。';
            Common::adminLog($request, $logInfo);
        }

        if($result !== false){
            return json(['code' => 1, 'msg' => '删除成功！']);
        }else{
            return json(['code' => 0, 'msg' => '删除失败']);
        }
    }

    /**
     * 检查调研项目排序是否已存在
     */
    public function checkProjectSorting(){
        $request = $this->request;
        $param = $request->param();
        $model = model('Researchproject');
        $researchId = $param['researchId'];
        $sorting = $param['sorting'];
        $info = $model->where(['researchId' => $researchId, 'sorting' => $sorting])->field('sorting')->find();

        if($info){
            return json(['status' => 1]);
        }else{
            return json(['status' => 0]);
        }
    }

}