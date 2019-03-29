<?php 

namespace app\admin\controller;

use app\common\Common;

use think\Request;

class Article extends Base
{

    /**
     * 评论 列表
     * @return \think\response\Json
     */
    public function commentList(){
        $request = $this->request;
        $param = $request->param();
        $model = model('Articlecomment');

        // 每页起始条数
        $start = $param['start'];
        // 每页显示条数
        $length = $param['length'];

        if(empty($param['articleId'])){
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
            'a.articleId' => $param['articleId']
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
            $model = model('Articlecomment');
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
        $model = model('Articlelike');

        // 每页起始条数
        $start = $param['start'];
        // 每页显示条数
        $length = $param['length'];

        if(empty($param['articleId'])){
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
            'a.articleId' => $param['articleId']
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