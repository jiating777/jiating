<?php

namespace app\admin\controller;

use app\common\BaseHelper;
use think\Request;
use think\Db;
use app\admin\model\Operator;

class Town extends Base
{

    public function _initialize() {
        parent::_initialize();
        $this->model = 'villages';
        $this->redirect = 'admin/town/index';
        if(session('ADMIN')['type'] == 4) {  //镇级管理员
            $this->defaultWhere = ['a.id'=>session('ADMIN')['villageId'],'parentId'=>'0'];
        } else {
            $this->defaultWhere = ['parentId'=>'0'];
        }
    }

    public function index(){
        // return parent::index();
        $list = model($this->model)->alias('a')->join('operator b','a.id=b.villageId')->field('a.*,b.loginName')->where($this->defaultWhere)->select();
        $this->assign('datas',$list);
        return $this->fetch($this->indexView);
    }

    public function savePost(Request $request, $redirect='') {
        $model = model($this->model);
        $param = $request->param();
        if($param['id']) {  //编辑
            // return $param;
            Db::startTrans();
            try {
                model($this->model)->where('id',$param['id'])->update(['name'=>$param['name']]);
                $data = [
                    'loginName' => $param['loginName'],
                    'updateDate' => time(),
                    'updateOper' => $this->admin->id
                ];
                if(isset($param['password']) && !empty($param['password'])) {
                    $data['password'] = BaseHelper::passwordEncrypt($param['password']);
                }
                Operator::where(['memberId'=>'0','type'=>4,'relateId'=>$param['id']])->update($data);
                Db::commit();
                return $this->success('保存成功！', $this->redirect);
            } catch (Exception $e) {
                Db::rollback();
                return $this->error('保存失败！');
            }
            
        } else {
            $id = BaseHelper::getUUID();  //镇ID
            $data = [
                'id' => BaseHelper::getUUID(),
                'memberId' => '0',
                // 'code' => $param['code'],
                'loginName' => $param['loginName'],
                'password' => BaseHelper::passwordEncrypt($param['password']),
                'createDate' => time(),
                'createOper' => $this->admin->id,
                'relateId' => $id,
                'type' => 4
            ];
            Db::startTrans();
            $addo = Operator::insert($data);

            $townData = ['id'=>$id,'name'=>$param['name'],'level'=>4];
            $addv = model($this->model)->insert($townData);
            if($addo && $addv) {
                Db::commit();
                return $this->success('添加成功！', $this->redirect);
            }
            Db::rollback();
            return $this->error('添加失败！');
        }
        
        $addo = Operator::insert($data);
        unset($param['loginName']);
        unset($param['password']);
        unset($param['imgId']);
        $param['id'] = $villageId;
        $param['updateDate'] = time();
        $addv = model($this->model)->insert($param);
        if($addo && $addv) {
            Db::commit();
            return $this->success('添加成功！', 'admin/' . strtolower($this->model) . '/index');
        }
        Db::rollback();
        return $this->error('添加失败！');
    }


}