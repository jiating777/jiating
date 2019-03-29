<?php

namespace app\admin\controller;

use app\common\BaseHelper as Helper;
use app\common\Common;
use app\admin\model\Image;
use app\admin\model\Area;
use app\admin\model\Member;
use app\admin\model\User;

use think\Request;

class Communitydynamics extends Base
{

    protected $isIframe = false;

    public function _initialize()
    {
        $this->exceptAction = [''];

        parent::_initialize();

        $defaultWhere = $this->getDefaultWhere();
        $defaultWhere['isDelete'] = 2;
        $this->defaultWhere = $defaultWhere;
        $this->searchFields = [
            'content' => [
                'label'     => '内容',
                'field'     => 'content',
                'type'      => 'text',
                'disabled'  => false,
                'condition' => 'like'
            ],
            'isPass' => [
                'label'     => '审核状态',
                'field'     => 'isPass',
                'type'      => 'select',
                'disabled'  => false,
                'data' => [
                    '' => '所有',
                    '0' => '未审核',
                    '1' => '审核通过',
                    '2' => '审核未通过',
                ]
            ],
        ];

        // 发布动态审核开关
        $dongtaicheckoff = 0;
        if($this->admin->townId){
            $config = db('townconfig')->where(['townId' => $this->admin->townId])->value('dongtaicheckoff');
            $dongtaicheckoff = $config;
        }
        $this->assign('dongtaicheckoff', $dongtaicheckoff);
    }

    public function index(){
        $request = $this->request;
        $param = $request->param();
        // Reset filter
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
            $list = $model->where($where)->limit($start, $length)->order($order)->select();
            foreach ($list as $item){
                $user = User::where(['id' => $item->createUser])->field('nickName as name, avatarUrl as avatar')->find();
                $item->user = $user;
            }
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

        return $this->fetch($this->indexView, [
            'pageSize' => ['url' => fullUrl($request->path())],
            'searchFields' => $this->searchFields,
            'param' => $request->param()]
        );
    }

    /**
     * Add
     */
    public function add(Request $request){
        // 单位
        $unitList = get_product_unit();
        $this->assign('unitList', $unitList);

        return parent::add($request);
    }

    /**
     * Add Post
     */
    public function addPost(Request $request, $redirect = ''){
        $model = model($this->model);

        //save data
        if ($request->isAjax()) {
            $data = $request->param();
            // Insert data
            $data['id'] = Helper::getUUID();
            $data['createDate'] = time();

            $result = $model->save($data);

            if($result !== false) {
                // 多图
                if(isset($data['imgIds']) && !empty($data['imgIds'])){
                    foreach ((array)$data['imgIds'] as $item) {
                        Image::where('id', $item)->update(['relatedId' => $data['id'], 'relatedTable' => 'Communitydynamics', 'tag' => 'imglist']);
                    }
                }
                // 详情图
                if(isset($data['detailImgIds']) && !empty($data['detailImgIds'])){
                    foreach ((array)$data['detailImgIds'] as $detailImgId) {
                        Image::where('id', $detailImgId)->update(['relatedId' => $data['id'], 'relatedTable' => 'Communitydynamics']);
                    }
                }

                // Query执行后的操作
                $model->_after_insert($data);

                // 写入日志
                $logInfo = $this->admin->name . '添加了一条' . $this->model . '数据。';
                Common::adminLog($request, $logInfo);

                if ($redirect) {
                    return $this->success('添加成功！', $redirect);
                } else {
                    return $this->success('添加成功！', 'admin/' . strtolower($this->model) . '/index');
                }
            } else {
                return $this->error($model->getError());
            }
        } else {
            return $this->error('');
        }
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
        $join = [
            ['__MEMBER__ m', 'a.memberId = m.id'],
        ];
        $fields = 'a.*, m.isParty';
        $userInfo = User::alias('a')->where(['a.id' => $info->createUser])->join($join)->field($fields)->find();
        $info->user = $userInfo;

        $areaMdl = new Area();
        $city = Helper::makeOptions(
            $areaMdl,
            ['level' => 1],
            ['id' => 'asc']
        );
        $this->assign('city',$city);

        $this->assign('area', $this->getArea($info));

        // 图集
        $imagesList = Image::where(['relatedId' => $info->id, 'tag' => 'imglist'])->select();
        $this->assign('imagesList', $imagesList);

        return $this->view->fetch($this->editView, [
            'info' => $info
        ]);
    }

    /**
     * Edit Post
     */
    public function editPost(Request $request, $redirect = ''){
        $model = model($this->model);

        //save data
        if ($request->isAjax()) {
            $data = $request->param();
            // Update data
            $data['updateDate'] = time();

            $result = $model->allowField(true)->save($data, ['id' => $data['id']]);

            if($result !== false) {
                // 多图
                if (isset($data['imgIds']) && !empty($data['imgIds'])) {
                    foreach ((array)$data['imgIds'] as $item) {
                        Image::where('id', $item)->update(['relatedId' => $data['id'], 'relatedTable' => 'Communitydynamics', 'tag' => 'imglist']);
                    }
                }
                // 详情图
                if (isset($data['detailImgIds']) && !empty($data['detailImgIds'])) {
                    foreach ((array)$data['detailImgIds'] as $detailImgId) {
                        Image::where('id', $detailImgId)->update(['relatedId' => $data['id'], 'relatedTable' => 'Communitydynamics']);
                    }
                }

                // Query执行后的操作
                $model->_after_update($data);

                // 写入日志
                $logInfo = $this->admin->name . '更新了一条' . $this->model . '数据。';
                Common::adminLog($request, $logInfo);

                if ($redirect) {
                    return $this->success('编辑成功！', $redirect);
                } else {
                    return $this->success('编辑成功！', 'admin/' . strtolower($this->model) . '/index');
                }
            } else {
                return $this->error($model->getError());
            }
        } else {
            return $this->error('error !');
        }
    }

    public function detail(Request $request) {
        $model = model($this->model);
        $id = $request->param('id');
        // Get data info
        $info = $model->find($id);

        if(!$info){
            return $this->error('error !', url($this->redirect));
        }
        $userInfo = User::where(['id' => $info->createUser])->find();
        $info->user = $userInfo;

        // 图集
        $imagesList = Image::where(['relatedId' => $info->id, 'tag' => 'imglist'])->select();
        $this->assign('imagesList', $imagesList);

        return $this->view->fetch('detail', [
            'info' => $info
        ]);
    }

    /**
     * 审核操作
     */
    public function checkPass(Request $request){
        if($request->isAjax()) {
            $model = model($this->model);

            $id = $request->param('id');
            $data = [
                'isPass' => $request->param('isPass')
            ];
            $result = $model->save($data, ['id' => $id]);

            if($result !== false) {
                return json(['code' => 1, 'msg' => '操作成功！', 'url' => url('admin/' . strtolower($this->model) . '/index')]);
            } else {
                return json(['code' => 0, 'msg' => $model->getError()]);
            }
        } else {
            return json(['code' => 0, 'msg' => '请求方式不正确！']);
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

            if(isset($filter['typeId']) && $filter['typeId']){
                $where['typeId'] = $filter['typeId'];
            }
            if(isset($filter['varietieId']) && $filter['varietieId']){
                $where['varietieId'] = $filter['varietieId'];
            }
            if(isset($filter['content']) && $filter['content']){
                $where['content'] = ['like', '%'.$filter['content'].'%'];
            }
            if(isset($filter['isPass']) && $filter['isPass'] != ''){
                $where['isPass'] = $filter['isPass'];
            }
        }

        return $where;
    }

    /**
     * 用户列表
     * @return \think\response\Json
     */
    public function userlist(){
        $request = $this->request;
        $param = $request->param();
        $model = model('User');

        // 每页起始条数
        $start = $param['start'];
        // 每页显示条数
        $length = $param['length'];

        $where = [];
        if(isset($param['townId']) && $param['townId']) {
            $where['m.townId'] = $param['townId'];
        }
        if(isset($param['villageId']) && $param['villageId']) {
            $where['m.villageId'] = $param['villageId'];
        }
        if(isset($param['isParty']) && $param['isParty']) {
            $where['m.isParty'] = $param['isParty'];
        }
        if($param['search']['value']) {
            $filter = json_decode($param['search']['value'],true);
            if(isset($filter['name']) && $filter['name']){
                $where['a.nickName'] = ['like', '%'.$filter['name'].'%'];
            }
        }
        $join = [
            ['__MEMBER__ m', 'a.memberId = m.id'],
            ['__ORGANIZATION__ o', 'm.organizationId = o.id'],
        ];
        $fields = 'a.id, a.nickName as name, a.avatarUrl as avatar, m.gender, o.name as organization';
        $list = $model->alias('a')->where($where)->join($join)->field($fields)->limit($start, $length)->select();
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
     * 评论 列表
     * @return \think\response\Json
     */
    public function commentList(){
        $request = $this->request;
        $param = $request->param();
        $model = db('articlecomment');

        // 每页起始条数
        $start = $param['start'];
        // 每页显示条数
        $length = $param['length'];

        if(empty($param['itemId'])){
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
            'a.isDelete' => 2,
            'a.articleId' => $param['itemId']
        ];

        $join = [
            ['__USER__ u', 'a.userId = u.id'],
        ];
        $field = 'a.*, u.nickName, u.avatarUrl';
        $list = $model->alias('a')->where($where)->limit($start, $length)->join($join)->field($field)->select();
        $count = $model->alias('a')->where($where)->join($join)->field($field)->count();

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
     * 删除评论
     */
    public function delComment(){
        $request = $this->request;
        if($this->request->isAjax()) {
            $model = db('articlecomment');
            $id = $this->request->param('id');

            try {
                $info = $model->find(['id', $id]);
                if(!$info){
                    return json(['code' => 0, 'msg' => '该评论不存在！']);
                }
                $result = $model->save(['isDelete' => 1], ['id' => $id]);

                if($result !== false) {
                    // 写入日志
                    $logInfo = $this->admin->loginName . '删除了1条评论数据。';
                    Common::adminLog($request, $logInfo);

                    return json(['code' => 1, 'msg' => '删除成功！']);
                } else {
                    return json(['code' => 0, 'msg' => '删除失败！']);
                }
            } catch (Exception $e) {
                return json(['code' => 0, 'msg' => $e->getMessage()]);
            }
        } else {
            return json(['code' => 0, 'msg' => '请求方式不正确！']);
        }
    }

    /**
     * 点赞 列表
     * @return \think\response\Json
     */
    public function likeList(){
        $request = $this->request;
        $param = $request->param();
        $model = db('articlelike');

        // 每页起始条数
        $start = $param['start'];
        // 每页显示条数
        $length = $param['length'];

        if(empty($param['itemId'])){
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
            'a.isDelete' => 2,
            'a.articleId' => $param['itemId']
        ];

        $join = [
            ['__USER__ u', 'a.userId = u.id'],
        ];
        $field = 'a.*, u.nickName, u.avatarUrl';
        $list = $model->alias('a')->where($where)->limit($start, $length)->join($join)->field($field)->select();
        $count = $model->alias('a')->where($where)->join($join)->field($field)->count();

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