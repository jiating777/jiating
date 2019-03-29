<?php

namespace app\admin\controller;

use app\common\BaseHelper as Helper;
use app\common\Common;
use app\admin\model\Image;
use app\admin\model\Area;
use app\admin\model\Villages;

use think\Request;

/**
 * 在线考试
 */
class Onlineexam extends Base
{

    public function _initialize()
    {
        parent::_initialize();

        $defaultWhere = $this->getDefaultWhere();
        $this->defaultWhere = $defaultWhere;

        $this->searchFields = [
            'title' => [
                'label'     => '考试名称',
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
            $examDB = db('examquestions');
            foreach ($list as $item) {
                $totalExams = $examDB->where(['examId' => $item->id])->count();
                $totalScore = $examDB->where(['examId' => $item->id])->sum('score');
                $item->totalExams = $totalExams;
                $item->totalScore = $totalScore;
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
                    common::adminLog($request, $logInfo);

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
                // Query执行后的操作
                $model->_after_insert($data);

                // 写入日志
                $logInfo = $this->admin->name . '添加了一条' . $this->model . '数据。';
                common::adminLog($request, $logInfo);

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

        // 考题
        $examDB = db('examquestions');
        $totalExams = $examDB->where(['examId' => $id])->count();
        $totalScore = $examDB->where(['examId' => $id])->sum('score');
        $info->totalExams = $totalExams;
        $info->totalScore = $totalScore;
        $minute = floor((strtotime($info->endTime)-strtotime($info->startTime)) % 86400 / 60);
        $info->examTime = $minute;
        $info->examquestions = $examDB->where(['examId' => $id])->order('number ASC')->select();

        // 参加考试
        $joinexamDB = db('joinexam');
        $totalJoins = $joinexamDB->where(['examId' => $id])->count();
        $totalPass = $joinexamDB->where(['examId' => $id])->where(['score' => ['egt', $info->passingGrade]])->count();
        $average = $joinexamDB->where(['examId' => $id])->avg('score');
        $info->totalJoins = $totalJoins;
        $info->totalPass = $totalPass;
        $info->average = number_format($average, 2);;

        return $this->view->fetch('detail', [
            'info' => $info
        ]);
    }

    /**
     * 参加考试 列表
     * @return \think\response\Json
     */
    public function joinexamList(){
        $request = $this->request;
        $param = $request->param();
        $model = model('Joinexam');

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
            'examId' => $param['id']
        ];

        $list = $model->alias('a')->where($where)->limit($start, $length)->order('score DESC')->select();
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
     * 查看参考人及考试成绩
     */
    public function viewJoinexam(Request $request){
        $model = model('Joinexam');
        $id = $this->request->param('id');
        $info = $model->where(['id' => $id])->find();
        if(!$info){
            return json(['code' => 0, 'msg' => 'error']);
        }

        // 考试成绩
        $examresultsDB = db('examresults');
        $totals = $examresultsDB->where(['joinId' => $id])->count();
        $totalCorrects = $examresultsDB->where(['joinId' => $id])->where(['isCorrect' => 1])->count();
        $info->totals = $totals;
        $info->totalCorrects = $totalCorrects;

        $join = [
            ['__EXAMQUESTIONS__ e', 'a.questionId = e.id'],
        ];
        $field = 'a.*, e.*';
        $info->examresults = $examresultsDB->alias('a')->where(['a.joinId' => $id])->join($join)->field($field)->order('e.number ASC')->select();

        $this->assign('info', $info);

        return json(['code' => 1, 'data' => $this->fetch('onlineexam/viewexamresults')]);
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
     * 考题
     */
    public function examquestions(Request $request){
        $request = $this->request;
        if($request->isAjax()){
            $param = $request->param();
            $model = model('Examquestions');

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
                'examId' => $id
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
     * 查看考题 添加/编辑
     */
    public function viewExam(Request $request){
        $model = model('Examquestions');
        $info = [];
        $id = $this->request->param('id');
        if($id){
            $info = $model->where(['id' => $id])->find();
            /*if($info){
                $info->option = json_decode($info->option, true);
            }*/
        }

        $this->assign('info', $info);

        return json(['code' => 1, 'data' => $this->fetch('onlineexam/viewexam')]);
    }

    /**
     * 保存考题 添加/编辑 提交
     */
    public function saveExam(Request $request){
        $model = model('Examquestions');

        //save data
        if ($request->isAjax()) {
            $data = $request->param();
            $data = $this->handleData($data);

            if(!$data['examId']){
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
                    $logInfo = $this->admin->name . '更新了一条考题数据。';
                    common::adminLog($request, $logInfo);

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
                $logInfo = $this->admin->name . '添加了一条考题数据。';
                common::adminLog($request, $logInfo);

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
        $correctAnswer = '';
        $isCorrect = [];
        switch($data['type']){
            case 1 :
                $NO = $data['option_NO1'];
                $isCorrect = $data['option_isCorrect1'];
                $value = $data['option_value1'];
                break;
            case 2 :
                $NO = $data['option_NO2'];
                $isCorrect = $data['option_isCorrect2'];
                $value = $data['option_value2'];
                break;
            case 3 :
                $NO = $data['option_NO3'];
                $isCorrect = $data['option_isCorrect3'];
                $value = $data['option_value3'];
                break;
            default : break;
        }
        foreach ($isCorrect as $key => $val) {
            if(!trim($val)){
                continue;
            }
            $options[] = [
                'option_NO' => $NO[$key],
                'option_isCorrect' => $val,
                'option_value' => $value[$key],
            ];
            if($val == 1){
                $correctAnswer .= ',' . $NO[$key];
            }
        }
        $data['option'] = json_encode($options);
        $data['correctAnswer'] = substr($correctAnswer, 1);

        return $data;
    }

    /**
     * 删除考题
     */
    public function delExam(){
        $model = model('Examquestions');

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

            $logInfo = $this->admin->name . '删除了1条考题数据。';
            common::adminLog($request, $logInfo);
        }

        if($result !== false){
            return json(['code' => 1, 'msg' => '删除成功！']);
        }else{
            return json(['code' => 0, 'msg' => '删除失败']);
        }
    }

    /**
     * 检查考题题号是否已存在
     */
    public function checkExamNumber(){
        $request = $this->request;
        $param = $request->param();
        $model = model('Examquestions');
        $examId = $param['examId'];
        $number = $param['number'];
        $info = $model->where(['examId' => $examId, 'number' => $number])->field('number')->find();

        if($info){
            return json(['status' => 1]);
        }else{
            return json(['status' => 0]);
        }
    }

}