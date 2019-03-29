<?php 

/**
 * 精准扶贫-扶贫干部
 */

namespace app\admin\controller;

use \think\Request;
use app\common\Common;
use app\common\BaseHelper as Helper;

use app\admin\model\Operator;
use app\admin\model\Povertymember;
use app\admin\model\Area;
use app\admin\model\Member;

class Povertyparty extends Base{

    public function _initialize() {
        parent::_initialize();
        $this->model = 'partymember';
        // $this->defaultWhere = ['villageId'=>session('ADMIN')['villageId']];
        $this->defaultWhere = ['isAid'=>'1'];
        $this->defaultOrder = 'createDate DESC';
    }

    public function index() {
        return parent::index();
    }

    //选择扶贫干部-提交
    public function addpost(Request $request, $redirect = '') {
        $model = model($this->model);
        $param = $request->param();
        // return $param;
        $data = [];
        foreach ((array)$param['memberId'] as $v) {
            $member = Member::where('id',$v)->field('cityId,xianId,townId,villageId')->find();
            $tmp = [
                'id' => Helper::getUUID(),
                'memberId' => $v,
                'cityId' => $member['cityId'],
                'xianId' => $member['xianId'],
                'townId' => $member['townId'],
                'villageId' => $member['villageId'],
                'createDate' => time(),
                'createOper' => $this->admin->id
            ];
            $data[] = $tmp;
        }
        $add = $model->insertAll($data);
        if($add) {
            return $this->success('添加成功！', 'admin/' . strtolower($this->model) . '/index');
        } else {
            return $this->error('添加失败，请重试', 'admin/' . strtolower($this->model) . '/index');
        }
    }

    public function delete() {  //若其有正在帮扶的对象，不允许删除
        $model = model($this->model);
        $request = $this->request;
        $id = $request->param('id');
        $party = $model->get($id);

        $row = Povertymember::where('aidingId',$party['memberId'])->find();
        if($row) {
            return $this->error('有帮扶对象，不能删除', 'admin/' . strtolower($this->model) . '/index');
        }
        $result = $model->where('id', $id)->delete();

        if($result !== false){
            // Query执行后的操作
            $model->_after_delete($id);

            $logInfo = $this->admin->name . '删除了1条' . $this->model . '数据。';
            Common::adminLog($request, $logInfo);
        }
        return $this->success('删除成功！', 'admin/' . strtolower($this->model) . '/index');


        // return parent::delete();
    }

}