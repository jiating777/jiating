<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\Menu;
use app\common\BaseHelper as Helper;
use app\common\Common;

use think\Cache;
use think\Request;
use think\Controller;
use think\exception\Handle;

use app\admin\model\Villages;
use app\admin\model\Area;

class Base extends Controller
{

    protected $admin = ''; //Admin
    protected $menu = null; //Current Menu Obj
    protected $model = ''; //Model Name
    protected $exceptAction = []; // 不需要验证权限的action
    protected $accessAction = ['index']; // 需要验证权限的action
    protected $access = [];
    protected $indexView = 'list'; //Index View
    protected $addView = 'add'; //Add View
    protected $editView = 'edit'; //Edit View
    protected $defaultWhere = []; //default where
    protected $defaultOrder = '';
    protected $searchFields = []; //normal search
    protected $pageSize = ['20', '30', '50', '100', '200'];
    protected $primaryKey = 'id'; //primaryKey
    protected $view = '';

    // 所属村ID
    protected $villageId = '';

    public function _initialize()
    {
        parent::_initialize();

        //dump(Helper::getUUID());die;
        $module = $this->request->module();
        $controller = $this->request->controller();
        //$action = $this->request->action();
        $action = 'index';
        // 获取当前菜单
        $this->menu = Menu::where('url', $module . '/' . $controller . '/' . $action)->find();

        // 获取当前model
        $this->model = $this->request->controller();
        $this->assign('model', $this->model);

        // check route auth
        $this->authCheck();

        $this->assign('admin', $this->admin);

        // $this->assign('selectarea',$this->getThis());

        // Get local location
        $this->assign('bcn', $this->getLocation());
        // Get menu (初始化后台菜单)
        $this->assign('sidebar', $this->menuHtml($this->menuJson()));
        // Get tabs
        $this->assign('tabs', $this->getTabs());
        $this->assign('currentUrl', $this->request->url());
    }

    /**
     * Check route auth
     */
    private function authCheck()
    {
        $session_admin = session('ADMIN');
        
        if(!empty($session_admin->id)){
            $this->admin = $session_admin;
            //$this->villageId = $this->admin->villageId;

            if(!$this->checkAccess()){
                $this->error('您没有访问权限！');
            }
            $this->assign('adminInfo', $session_admin);
        }else{
            if($this->request->isAjax()){
                $this->error('您没有访问权限！', url('/login'));
            }else{
                $this->redirect('/login');
            }
        }
    }

    /**
     * 权限检查
     * @return bool
     */
    private function checkAccess() {
        return true;
    }

    /**
     * Cache menu
     *
     * @param null $data
     * @return bool|null
     */
    public function menuCache($data = null) {
        if (empty($data)) {
            $data = Menu::getTree(0);
            // 缓存
            Cache::set('admin_menu', $data, 3600*6);
        } else {
            Cache::set('admin_menu', $data, 3600*6);
        }
        return $data;
    }

    /**
     * 菜单树状结构集合
     */
    public function menuJson() {
        $menu = Cache::get('admin_menu');
        if (!$menu) {
            $menu = $this->menuCache();
        }
        return $menu;
    }

    /**
     * 菜单
     * @param $menus
     * @return string
     */
    public function menuHtml($menus) {
        if($menus){
            $this->access = explode(',', $this->admin->menuId);
            $subMenu = function ($menus, $level = 0) use (&$subMenu) {
                $output = '';
                foreach($menus as $menu){
                    // 检查菜单权限
                    // if ($this->admin->memberId != '0' && !in_array($menu['id'], $this->access)) {
                    //     continue;
                    // }
                    //检测特殊权限  TODO

                    $uri = $this->request->path();
                    $uri_arr = explode('/', $uri);
                    count($uri_arr) < 2 && $uri_arr[1] = 'index';
                    if(count($uri_arr) < 3 || preg_match('/add/', $uri_arr[2]) || preg_match('/edit/', $uri_arr[2]) || (isset($uri_arr[3]) && $uri_arr[3] == 'id')){
                        $uri_arr[2] = 'index';
                    }
                    if($menu['parent']){
                        $icon = !empty($menu['icon']) ? $menu['icon'] : '';
                    }else{
                        $icon = $menu['icon'];
                    }
                    if(('/'.$uri_arr[0] == strtolower($menu['url']))){
                        $active = 'active';
                    }else{
                        $active = '';
                    }

                    if(empty($menu['items'])){
                        $output .= '<li class="nav-item '.$active.'">';
                        $output .= '<a href="'.$menu['url'].'" class="nav-link" target="'.$menu['target'].'">';
                        $output .= '<i class="'.$icon.'"></i>';
                        $output .= '<span class="title"> '.lang($menu['name']).' </span>';
                        if($uri_arr[1] != 'menu') {
                            if(isset($this->menu->id) && $menu['id'] == $this->menu->id){
                                $output .= '<span class="selected"></span>';
                            }
                        }
                        $output .= '</a>';
                        $output .= '</li>';
                    }else{
                        $module = $this->request->module();
                        $controller = $this->request->controller();
                        //$action = $this->request->action();
                        $action = 'index';
                        $current = Menu::where(['url' => $module . '/' . $controller . '/' . $action])->find();
                        $parentIds = $this->getParentIds($current['parentId']);
                        $parentIdsArr = explode(',' , $parentIds);
                        $class = 'active';
                        if($level >= 2){
                            $class .= ' open';
                        }
                        if($uri_arr[1] == 'menu') {
                            $paramId = $this->request->param('id');
                            $parentIdsArr[] = $paramId;
                        }
                        $output .= '<li class="nav-item '.(in_array($menu['id'], $parentIdsArr) ? $class : '').'">';
                        // $menu['url'] = 'javascript:void(0);';
                        $output .= '<a href="'.$menu['url'].'" class="nav-link nav-toggle" target="'.$menu['target'].'">';
                        $output .= '<i class="'.$icon.'"></i>';
                        $output .= '<span class="title"> '.lang($menu['name']).' </span>';
                        if(in_array($menu['id'], $parentIdsArr)){
                            $output .= '<span class="selected"></span>';
                            $output .= '<span class="arrow open"></span>';
                        }
                        $output .= '</a>';
                        $output .= '<ul class="sub-menu">';
                        $output .= $subMenu($menu['items'], $level + 1);
                        $output .= '</ul>';
                        $output .= '</li>';
                    }
                }
                return $output;
            };
            $output = $subMenu($menus);

            return $output;
        }
    }

    // 得到父辈、爷辈、祖辈...的集合
    public function getParentIds($parentId, $level = 0) {
        $result = Menu::where(['id' => $parentId])->find();
        $parentIds = $result['id'];

        if($result['parentId']){
            $level = $level + 1;
            $parentIds .= ',' . $this->getParentIds($result['parentId'], $level);
        }
        return $parentIds;
    }

    private function getLocation() {
        //BCN
        $bcn = '';
        if ($this->menu) {
            //set page title with translation
            $trans_title_key = strtolower(str_replace(' ', '_', $this->menu->name));
            if (lang($trans_title_key) == $trans_title_key) {
                $this->assign('title', $this->menu->name);
            } else {
                $this->assign('title', lang( $trans_title_key));
            }

            //set bcn by Closure menu
            $recursive = function ($id, $output) use (&$recursive) {
                $menu = Menu::find($id);
                $trans_title_key = strtolower(str_replace(' ', '_', $menu->name));
                $title = $menu->name;
                if (lang($trans_title_key) != $trans_title_key) {
                    $title = lang($trans_title_key);
                }
                $link = '<li>';
                if ($this->menu->id == $id) {
                    $link .= '<i class="fa fa-angle-right"></i>&nbsp;<span>' . $title . '</span>';
                } else {
                    if($menu->parentId){
                        // TODO
                        $menu->route = $menu->url != '#' ? strtolower($menu->url) : 'javascript:void(0);';
                        $target = $menu->target ? 'target="'.$menu->target.'"' : '';
                        $link .= '<a href="'.url($menu->url).'" title="'.$title.'" '.$target.'>'.$title.'</a>';
                    }else{
                        $link .= '<i class="fa fa-angle-right"></i>&nbsp;<span>' . $title . '</span>';
                    }
                }
                $link .= '</li>';

                array_unshift($output, $link);

                if ($menu->parentId) {
                    $output = $recursive($menu->parentId, $output);
                }
                return $output;
            };

            $bcn = $this->view->fetch('public/bcn', ['breadcrumbs' => $recursive($this->menu->id, [])]);
            return $bcn;
        }
    }

    private function getTabs() {
        //tabs
        $tabs = '';
        if ($this->menu) {
            $menus = Menu::where(['parentId' => $this->menu->parentId, 'status' => 1])->order('sorting', 'asc')->order('id', 'asc')->select();
            if ($menus) {
                foreach ($menus as $menu) {
                    //title
                    $trans_title_key = strtolower(str_replace(' ', '_', $menu->name));
                    $title = $menu->name;
                    if (lang($trans_title_key) != $trans_title_key) {
                        $title = lang($trans_title_key);
                    }
                    $tabs .= '<li' . ($this->menu->id == $menu->id ? ' class="active"' : '') . '>';
                    $menu->route = strtolower($menu->url);
                    $tabs .= '<a href="' . url($this->request->path(), $this->request->param('id') ? $this->request->param('id') : '') . '" title="' . $title . '">' . $title . '</a>';
                    $tabs .= '</li>';
                }
            }
            return $tabs;
        }
    }

    /**
     * Show
     *
     * @param $template
     * @param array $data
     * @return mixed
     */
    private function show($template, $data = []) {

        return $this->view->fetch($template, $data);
    }

    /**
     * List
     */
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
                //$model = $model->where($this->defaultWhere);
                $where = array_merge($this->defaultWhere, $where);
            }
            /*if($this->defaultOrder){
                $model = $model->order($this->defaultOrder);
            }*/

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

        return self::show($this->indexView, [
            'pageSize' => ['url' => fullUrl($request->path())],
            'searchFields' => $this->searchFields,
            'param' => $request->param()
        ]);

    }

    /**
     * Add
     */
    public function add(Request $request){

        return self::show($this->addView, []);
    }

    /**
     * Add Post
     */
    public function addPost(Request $request, $redirect){
        $model = model($this->model);

        //save data
        if ($request->isPost()) {
            $data = $request->param();
            // return $data;
            // dump($data);die;
            // Insert data
            $data['id'] = Helper::getUUID();
            $data['createDate'] = time();
            $data['createOper'] = $this->admin->id;
            // $data['villageId'] = $this->admin->villageId;
            $result = $model->save($data);

            if($result !== false) {
                // Query执行后的操作
                $model->_after_insert($data);

                // 写入日志
                $logInfo = $this->admin->name . '添加了一条' . $this->model . '数据。';
                common::adminLog($request, $logInfo);

                if ($redirect) {
                    return $this->success('添加成功！', $redirect);
                } else {
                    return $this->success('添加成功！', 'admin/' . strtolower($this->model) . '/index');
                }
            } else {
                return $this->error($model->getError());
            }
        } else {
            return $this->error('添加失败！');
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

        return $this->show($this->editView, [
            'info' => $info
        ]);
    }

    /**
     * Edit Post
     */
    public function editPost(Request $request, $redirect){
        $model = model($this->model);

        //save data
        if ($request->isPost()) {
            $data = $request->param();
            // Update data
            $data['updateDate'] = time();
            $data['updateOper'] = $this->admin->id;
            $result = $model->allowField(true)->save($data, ['id' => $data['id']]);

            if($result !== false) {
                // Query执行后的操作
                $model->_after_update($data);

                // 写入日志
                $logInfo = $this->admin->name . '更新了一条' . $this->model . '数据。';
                common::adminLog($request, $logInfo);

                if ($redirect) {
                    return $this->success('编辑成功！', $redirect);
                } else {
                    return $this->success('编辑成功！', 'admin/' . strtolower($this->model) . '/index');
                }
            } else {
                return $this->error($model->getError());
            }
        } else {
            return $this->error('编辑失败！');
        }
    }

    /**
     * Delete
     */
    public function delete(){
        $model = model($this->model);

        $request = $this->request;
        $id = $request->param('id');
        if(isset($_POST['ids'])){
            // 删除多条
            $ids = $request->param('ids');

            $result = $model->whereIn('id', $ids)->delete();

            if($result !== false){
                // Query执行后的操作
                $model->_after_delete($ids);

                $logInfo = $this->admin->name . '删除了' . count($ids) . '条' . $this->model . '数据。';
                common::adminLog($request, $logInfo);
            }
        }else if($id){
            // 删除一条
            $info = $model->find(['id', $id]);
            if(!$info){
                return redirect('admin/'.$this->model.'/index');
            }
            //$result = $info->update(['status' => 90]);
            $result = $model->where('id', $id)->delete();

            if($result !== false){
                // Query执行后的操作
                $model->_after_delete($id);

                $logInfo = $this->admin->name . '删除了1条' . $this->model . '数据。';
                common::adminLog($request, $logInfo);
            }
        }

        if($result !== false){
            if(strtolower($request->controller()) != strtolower($this->model)){
                return $this->success('删除成功！', 'admin/' . strtolower($request->controller()) . '/index');
            }else{
                return $this->success('删除成功！', 'admin/' . strtolower($this->model) . '/index');
            }
        }else{
            return $this->error('删除失败！', 'admin/' . strtolower($this->model) . '/index');
        }
    }

    /**
     * Auto switch search model
     *
     * @param Request $request
     * @param $model
     * @return mixed
     */
    protected function autoSwitchSearch(Request $request, $model)
    {
        $this->searchFields = $this->searchFields;

        $params = array_merge($_GET, $_POST);
        if (!empty($params)) {
            if (!empty($this->searchFields)) {
                $whereIn = [];
                foreach ($this->searchFields as $field => $config) {
                    if (isset($params[$field])) {
                        $val = $params[$field];
                        if (is_string($val) && trim($val)) {
                            $condition = isset($config['condition']) ? $config['condition'] : '=';
                            if (in_array($config['type'], ['date', 'datetime'])) {
                                $val = strtotime($val);
                                if ($condition == '<') {
                                    $val += 86400;
                                } else if($condition == '>'){
                                    $val -= 86400;
                                }
                            }
                            if ($condition == 'like') {
                                $val = '%' . $val . '%';
                            }
                            if(in_array($config['type'], ['date', 'datetime']) && $condition == '='){
                                $model = $model->where($field, 'between time', [$val, $val + 86400]);
                            }else{
                                $model = $model->where($field, $condition, $val);
                            }
                        }
                    } else {
                        $params[$field] = '';
                    }
                }
                //where in search
                if (!empty($whereIn)) {
                    foreach ($whereIn as $key => $val) {
                        $model = $model->whereIn($key, $val);
                    }
                }
            }

            // starttime
            $start_time = isset($params['_starttime']) ? $params['_starttime'] : '';
            if (isset($start_time) && trim($start_time) && !empty($this->searchFields)) {
                $model = $model->where('createDate', '>=', strtotime($start_time));
            }
            // endtime
            $end_time = isset($params['_endtime']) ? $params['_endtime'] : '';
            if (isset($end_time) && trim($end_time) && !empty($this->searchFields)) {
                $model = $model->where('updateDate', '<=', strtotime($end_time));
            }

            $this->assign('params', $params);
        }

        return $model;
    }

    /**
     * 筛选条件
     */
    public function getFilterWhere($request){
        $param = $request->param();
        $where = [];
        if($param['search']['value']) {
            $filter = json_decode($param['search']['value'],true);
        }

        return $where;
    }

    /**
     * 默认条件
     */
    public function getDefaultWhere(){
        $defaultWhere = [];

        return $defaultWhere;
    }


}