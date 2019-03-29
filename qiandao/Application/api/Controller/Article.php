<?php

namespace app\api\controller;

use app\admin\model\Article AS ArticleMdl;
use app\admin\model\Articletype;
use app\admin\model\Articlecomment;
use app\admin\model\Articlelike;
use app\admin\model\Member;
use app\admin\model\Operator;
use app\admin\model\Area;

class Article extends BaseController
{
    public function test()
    {
        $typeList = Articletype::where('type', 'party')->select();
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $typeList);
    }
    /**
     * @return \think\response\Json
     * 文章列表
     */
    public function getArticle()
    {
        $param = self::getHttpParam();
        $start = 0;
        $length = 20;
        if (empty($param->villageId)) {
            // return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'villageId不能为空');
        }
        if (empty($param->townId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId不能为空');
        }
        if (empty($param->type)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'type不能为空');
        }

        $where = ['type' => $param->type];

        if (!empty($param->typeId)) {
            $where['typeId'] = $param->typeId;
        }

        if (!empty($param->start)) {
            $start = $param->start;
        }
        if (!empty($param->length)) {
            $length = $param->length;
        }
        
        $article1 = [];
        $total1 = 0;
        if($param->type != 'toutiao') {
            $xianId = Area::where('id',$param->townId)->value('parentId');
            $cityId = Area::where('id',$xianId)->value('parentId');
            if(!empty($param->villageId)) {
                $where['villageId'] = $param->villageId;
                $whereStr = '((level = 1 and cityId = '.$cityId.') OR (level = 2 and xianId = '.$xianId.') OR (level = 3 and townId = '.$param->townId.'))';
            } else {
                $where['townId'] = $param->townId;
                $whereStr = '((level = 1 and cityId = '.$cityId.') OR (level = 2 and xianId = '.$xianId.'))';
            }
            $where2 = ['type' => $param->type];
            if (!empty($param->typeId)) {
                $where2['typeId'] = $param->typeId;
            }
            $article1 = ArticleMdl::where($where2)->where($whereStr)->select();
            $total1 = ArticleMdl::where($where2)->where($whereStr)->count();
        }
        $article = ArticleMdl::where($where)->order('orderNo ASC')->limit($start, $length)->select();
        $article = array_merge($article,$article1);
        $total = ArticleMdl::where($where)->count();
        $total = $total + $total1;

        //组装operator数组，查找发布文章人所属组织，避免循环查询
        $operatorMap = Operator::getAllOrganization($param->townId);

        // if(!empty($param->search)) {  //根据关键字搜索
        //     $where['title'] = ['like','%'.$param->search.'%'];
        // }        

        if (empty($article)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到文章信息');
        }
        foreach ($article as $k => $a) {
            $is_praise = Articlelike::where(['articleId'=>$a['id'],'userId'=>$param->userId])->find();
            $article[$k]['is_praise'] = $is_praise ? 'true' : 'false';
            $article[$k]['articletypeName'] = $a['typeId'];
            $article[$k]['iconUrl'] = $a['iconUrl'].'?imageView2/1/w/135/h/90';
            $article[$k]['organization'] = isset($operatorMap[$a['createOper']]) ? $operatorMap[$a['createOper']][0] : $this->defaultOrganization;
        }

        return showTotal(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $article, $total);
    }

    public function getArticleType()
    {
        $param = self::getHttpParam();
        if (empty($param->type)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'type不能为空');
        }
        $typeList = Articletype::where('type', $param->type)->select();
        if (empty($typeList)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到分类信息');
        }
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $typeList);
    }

    /**
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * 文章详情
     */
    public function getArticleDetail()
    {
        $param = self::getHttpParam();
        if (empty($param->articleId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'articleId不能为空');
        } else {
            $article = ArticleMdl::where('id', $param->articleId)->find();
            if (empty($article)) {
                return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到文章信息');
            } else {
                $memberId = Operator::where(['id' => $article['createOper']])->value('memberId');
                if($memberId && $memberId != 0){
                    // 组织
                    $organization = Member::alias('a')->where(['a.id' => $memberId])->join('__ORGANIZATION__ o', 'a.organizationId = o.id')->value('o.name');
                    $article['organization'] = $organization;
                }else{
                    $article['organization'] = $this->defaultOrganization;
                }

                $is_praise = Articlelike::where(['articleId'=>$param->articleId,'userId'=>$param->userId])->find();
                $article['is_praise'] = $is_praise ? 'true' : 'false';
                $article['readCount'] += 1;

                // 每查看一次，阅读数量加1
                ArticleMdl::where('id', $param->articleId)->setInc('readCount');

                return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $article);
            }
        }
    }

    //便民服务，获取type为work的列表，返回格式以类为主体
    public function getWorkList()
    {
        $param = self::getHttpParam();
        if (empty($param->townId)) {
            return show(config('status.ERROR_STATUS'), self::NOT_PARAM, 'townId不能为空');
        }

        $xianId = Area::where('id',$param->townId)->value('parentId');
        $cityId = Area::where('id',$xianId)->value('parentId');
        $where = ['type'=>'work'];
        if(!empty($param->villageId)) {
            $where['villageId'] = $param->villageId;
            $whereStr = '((level = 1 and cityId = '.$cityId.') OR (level = 2 and xianId = '.$xianId.') OR (level = 3 and townId = '.$param->townId.'))';
        } else {
            $where['townId'] = $param->townId;
            $whereStr = '((level = 1 and cityId = '.$cityId.') OR (level = 2 and xianId = '.$xianId.'))';
        }
        
        $article1 = ArticleMdl::where(['type' => 'work'])->where($whereStr)->order('orderNo ASC')->select();
        $article = ArticleMdl::where($where)->order('orderNo ASC')->select();
        $article = array_merge($article,$article1);

        $tmp = [];
        foreach ($article as $k => $a) {
            $article[$k]['iconUrl'] = $a['iconUrl'].'?imageView2/1/w/65/h/65';
            $tmp[$a['typeId']][] = $article[$k];
        }
        $result = [];
        foreach ($tmp as $type => $data) {
            $result[] = [
                'type' => $type,
                'data' => $data
            ];
        }
        return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $result);
    }

}