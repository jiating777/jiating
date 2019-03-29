<?php 

/**
 * 综合管理-头条
 */

namespace app\admin\controller;

use app\admin\model\Article;
use app\admin\model\Articletype;
use app\admin\model\Image;
use app\admin\model\Area;

use \think\Request;
use app\lib\Qiniu;
use app\common\Common;
use app\common\BaseHelper as Helper;

class Articletoutiao extends Base
{

    public function __construct() {
        parent::__construct();
        $this->model = 'article';
        $this->addView = 'article:add';
        $this->editView = 'article:edit';
        $this->indexView = 'article:list'; //Index View
        $this->type = 'toutiao';
        $this->defaultWhere = ['type'=>$this->type];
        $this->redirect = 'admin/articletoutiao/index';
        $this->searchFields = [
            'title' => [
                'label'     => '标题',
                'field'     => 'title',
                'type'      => 'text',
                'disabled'  => false,
                'condition' => 'like'

            ],
        ];
        $this->assign('type',$this->type);
    }

    public function index() {
        return parent::index();
    }

    public function add(Request $request) {
        $typeList = Articletype::where('type',$this->type)->select();
        $this->assign('typeList',$typeList);
        return parent::add($request);
    }

    public function addPost(Request $request, $redirect = '') {
        return parent::addPost($request, $this->redirect);
    }

    public function edit(Request $request) {
        $areaMdl = new Area();
        $city = Helper::makeOptions(
            $areaMdl,
            ['level' => 1],
            ['id' => 'asc']
        );
        $this->assign('city',$city);

        $typeList = Articletype::where('type',$this->type)->select();
        $this->assign('typeList',$typeList);
        return parent::edit($request);
    }

    public function editPost(Request $request, $redirect = '') {
        return parent::editPost($request, $this->redirect);
    }

    public function delete() {
        return parent::delete();
    }

    public function detail(Request $request) {
        $model = model($this->model);
        $id = $request->param('id');
        // Get data info
        $info = $model->find($id);

        if(!$info){
            return $this->error('error !', url($this->redirect));
        }
        $info->organization = $this->getOrganization($info->createOper);

        return $this->view->fetch('article/detail', [
            'info' => $info
        ]);
    }

    /**
     * 筛选条件
     */
    public function getFilterWhere($request){
        $param = $request->param();
        $where = [];
        if($param['search']['value']) {
            $filter = json_decode($param['search']['value'],true);
            if(isset($filter['title']) && $filter['title']){
                $where['title'] = ['like', '%'.$filter['title'].'%'];
            }
        }

        return $where;
    }

}