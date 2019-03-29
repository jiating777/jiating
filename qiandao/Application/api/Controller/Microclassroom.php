<?php

namespace app\api\controller;

use app\common\BaseHelper;

use app\admin\model\Microclassroom as MicroclassroomMdl;
use app\admin\model\Classhour;

use think\Db;
use think\Exception;
use think\Request;

/**
 * 微课堂
 */
class Microclassroom extends BaseController
{

    /**
     * 获取课堂列表
     *
     * @return \think\response\Json
     */
    public function getMicroclassroomList()
    {
        $param = self::getHttpParam();
        $start = 0;
        $length = 20;

        if (empty($param->townId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId 不能为空');
        }
        if (!empty($param->start)) {
            $start = $param->start;
        }
        if (!empty($param->length)) {
            $length = $param->length;
        }

        $where = [
            'townId' => $param->townId
        ];
        if (isset($param->villageId) && $param->villageId) {
            $where = [
                'villageId' => $param->villageId
            ];
        }
        // 分类
        if (!empty($param->categoryId)) {
            $where['categoryId'] = $param->categoryId;
        }

        $fields = 'id, title, categoryId, imgUrl, createDate';
        $model = db('microclassroom');
        $result = $model->where($where)->limit($start, $length)->field($fields)->select();
        $total = $model->where($where)->count();

        if (empty($result)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到课堂');
        }

        foreach ($result as &$item) {
            $classHours = Classhour::where(['classId' => $item['id']])->count();
            $item['classHours'] = $classHours;
        }

        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result, $total);
    }

    /**
     * 获取课堂详情
     *
     * @return \think\response\Json
     */
    public function getMicroclassroomDetail()
    {
        $param = self::getHttpParam();

        if (empty($param->townId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId 不能为空');
        }

        if (empty($param->classId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'classId 不能为空');
        }
        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }

        $classId = $param->classId;
        $where = [
            'townId' => $param->townId,
            'id' => $classId
        ];
        if (isset($param->villageId) && $param->villageId) {
            $where = [
                'villageId' => $param->villageId
            ];
        }
        $fields = 'id, title, categoryId, imgUrl, content, createDate';

        $model = db('microclassroom');
        $item = $model->where($where)->field($fields)->find();

        if(empty($item)){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到相关课堂');
        }

        // 课程分类
        $item['category'] = show_microclass_category($item['categoryId']);
        unset($item['categoryId']);
        // 课时
        $field = 'id as classhourId, name, fileUrl, fileType';
        $classhour = Classhour::where(['classId' => $classId])->field($field)->order('sorting ASC')->select();
        $classHours = Classhour::where(['classId' => $classId])->count();
        if($classhour){
            foreach($classhour as &$value){
                if(!$value['fileType']){
                    @$value['fileType'] =pathinfo($value['fileUrl'], PATHINFO_EXTENSION);
                }
            }
        }
        $item['classHours'] = $classHours;
        $item['classhour'] = $classhour;
        // 是否已加入学习计划
        $isJoin = $this->getjoinClassroomInfo(['classId' => $classId, 'userId' => $param->userId]);
        if($isJoin){
            $isJoin = 1;
        }else{
            $isJoin = 2;
        }
        $item['isJoin'] = $isJoin;

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $item);
    }

    /**
     * 课程文件浏览
     */
    public function viewClasshourFile()
    {
        $param = self::getHttpParam();

        if (empty($param->classId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'classId 不能为空');
        }
        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }
        if (empty($param->classhourId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'classhourId 不能为空');
        }

        $classId = $param->classId;
        $userId = $param->userId;
        $info = $this->getjoinClassroomInfo(['classId' => $classId, 'userId' => $userId]);

        if(!$info){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '还没有加入学习计划');
        }

        $model = db('classroomresults');
        $where = [
            'joinId' => $info['id'],
            'classhourId' => $param->classhourId,
            'userId' => $userId
        ];
        $resultsInfo = $model->where($where)->find();
        if ($resultsInfo) {
            // 是否已经预览过
            if($resultsInfo['status'] == 1){
                return show(config('status.ERROR_STATUS'), self::NOT_DATA, '已经浏览过了');
            }

            $result = $model->where($where)->update(['status' => 1]);
        } else {
            $data = [
                'id' => BaseHelper::getUUID(),
                'createDate' => time(),
                'joinId' => $info['id'],
                'classhourId' => $param->classhourId,
                'userId' => $userId,
                'status' => 1
            ];
            $result = $model->insert($data);
        }

        if($result !== false){
            // 是否已完成所有课时
            unset($where['classhourId']);
            $totalClasshour = $model->where($where)->count();
            $where['status'] = 1;
            $countCompleted = $model->where($where)->count();
            if($totalClasshour == $countCompleted){
                $res = db('joinclassroom')->where(['classId' => $classId, 'userId' => $userId])->update(['status' => 1]);
            }

            return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result);
        }else{
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '课程文件浏览失败');
        }
    }

    /**
     * 获取课堂类型
     *
     * @return \think\response\Json
     */
    public function getMicroclassroomType()
    {
        $category = get_microclass_category();

        if (empty($category)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到课堂类型');
        }

        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $category);
    }

    /**
     * 获取加入学习计划信息
     */
    protected function getjoinClassroomInfo($data)
    {
        if(!$data['classId'] || !$data['userId']){
            return false;
        }
        $where = [
            'classId' => $data['classId'],
            'userId' => $data['userId']
        ];
        $model = db('joinclassroom');
        $info = $model->where($where)->find();
        if (!$info) {
            return false;
        }

        return $info;
    }

    /**
     * 加入学习计划
     */
    public function joinClassroom()
    {
        $param = self::getHttpParam();

        if (empty($param->classId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'classId 不能为空');
        }
        if (empty($param->userId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'userId 不能为空');
        }

        $classId = $param->classId;
        $info = $this->getjoinClassroomInfo(['classId' => $classId, 'userId' => $param->userId]);
        if($info){
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '已经加入学习计划了');
        }

        try {
            Db::startTrans();

            $model = db('joinclassroom');
            $data = [
                'id' => BaseHelper::getUUID(),
                'createDate' => time(),
                'classId' => $classId,
                'userId' => $param->userId,
                'status' => 0
            ];
            $result = $model->insert($data);

            if($result !== false){
                $classroomresults = db('classroomresults');
                $classhours = db('classhour')->where(['classId' => $classId])->field('id')->select();
                foreach($classhours as $item){
                    $item = (Array)$item;
                    $resData = [
                        'id' => BaseHelper::getUUID(),
                        'createDate' => time(),
                        'joinId' => $data['id'],
                        'classhourId' => $item['id'],
                        'userId' => $param->userId,
                        'status' => 0
                    ];

                    $res = $classroomresults->insert($resData);
                    if(!$res){
                        Db::rollback();
                        return show(config('status.ERROR_STATUS'), self::NOT_DATA, '加入学习计划失败，请稍后再试');
                    }
                }
                Db::commit();
                return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result);
            }else{
                Db::rollback();
                return show(config('status.ERROR_STATUS'), self::NOT_DATA, '加入学习计划失败');
            }
        } catch (Exception $e) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '加入学习计划失败，请稍后再试');
        }
    }

}