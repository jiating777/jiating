<?php

namespace app\api\controller;


use app\admin\model\Knowledge AS KnowledgeMdl;
use app\admin\model\Knowledgetype;
use app\admin\model\Articlecomment;
use app\admin\model\Articlelike;
use app\admin\model\Image;


class Knowledge extends BaseController
{
    /**
     * @return \think\response\Json
     * 农事知识列表
     */
    public function getList()
    {
        $param = self::getHttpParam();
        $start = 0;
        $length = 20;

        $where = [];
        if(!empty($param->typeId)) {  //根据专题分类查询
            $where['typeId'] = $param->typeId;
        }

        if(!empty($param->search)) {  //根据关键字搜索
            $where['title'] = ['like','%'.$param->search.'%'];
        }

        $list = KnowledgeMdl::where($where)->order('sorting,createDate DESC')->limit($start, $length)->select();
        $total = KnowledgeMdl::where($where)->count();
        
        if (empty($list)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到文章信息');
        }
        foreach ($list as $k => $a) {
            $list[$k]['imgUrl'] = $a['imgUrl'].'?imageView2/1/w/690/h/460';
        }
        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $list, $total);

    }

    //获取最新发布的6条
    public function getLastList() {
        $list = KnowledgeMdl::order('createDate DESC')->limit(0, 6)->select();
        foreach ($list as $k => $a) {
            $list[$k]['imgUrl'] = $a['imgUrl'].'?imageView2/1/w/690/h/460';
        }
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $list);
    }

    //农事知识专题列表
    public function getType()
    {
        $param = self::getHttpParam();
        $typeList = Knowledgetype::select();
        if (empty($typeList)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到分类信息');
        }
        foreach ($typeList as $k => $v) {
            $typeList[$k]['count'] = KnowledgeMdl::where('typeId',$v['id'])->count();
        }
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $typeList);
    }

    /**
     * 农事知识详情
     */
    public function getDetail()
    {
        $param = self::getHttpParam();
        if (empty($param->id)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'id不能为空');
        } else {
            $row = KnowledgeMdl::where('id', $param->id)->find();
            if (empty($row)) {
                return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到文章信息');
            } else {  //每查看一次，阅读数量加1
                KnowledgeMdl::where('id', $param->id)->setInc('readCount');
                $row['readCount'] += 1;
                $row['commentCount'] = Articlecomment::where('id',$param->id)->count();
                $row['image'] = Image::where(['relatedId' => $param->id, 'tag' => 'imglist'])->select();
                $row['likeCount'] = Articlelike::where('id',$param->id)->count();
                return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $row);
            }
        }
    }

}