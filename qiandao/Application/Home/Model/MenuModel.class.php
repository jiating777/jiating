<?php

namespace Home\Model;
use Think\Model;

class MenuModel extends Model
{

    // 数据表名称
    //protected $table;

    // 当前模型名称 不带前缀
    protected $name = 'menu';

    // 数据表主键 复合主键使用数组定义 不设置则自动获取
    protected $pk = 'id';

    // 字段验证规则
    protected $validate = true;


    /**
     * 按父ID查找菜单子项
     *
     * @param string $parentId 父菜单ID
     * @param bool $with_self 是否包括他自己
     * @return array
     */
    public static function getMenu($parentId, $with_self = false)
    {
        // 父节点ID
        $parentId = $parentId;
        $menuMdl = M('menu');
        $result = $menuMdl->where(['parentId' => $parentId])->select();
        if ($with_self) {
            $result2[] = $menuMdl->where(['id' => $parentId])->find();
            $result = array_merge($result2, $result);
        }

        return $result;
    }

    // 取得树形结构的菜单
    public static function getTree($myId, $parent = "", $Level = 1)
    {
        $data = self::getMenu($myId);
        $Level++;

        if (is_array($data)) {
            $ret = NULL;
            foreach ($data as $value) {
                $value = $value->data;
                $id = $value['id'];
                $uriArr = explode('/', $value['url']);
                $app = isset($uriArr['0']) ? ucwords($uriArr['0']) : '';
                $controller = isset($uriArr['1']) ? ucwords($uriArr['1']) : '';
                $action = isset($uriArr['2']) ? $uriArr['2'] : '';
                // 附带参数
                $array = array(
                    "app" => $app,
                    "controller" => $controller,
                    "action" => $action,
                    "icon" => $value['imgPath'],
                    "id" => $id,
                    "name" => $value['name'],
                    "target" => '_self',
                    "parent" => $parent,
                    "url" => url(strtolower($value['url']))
                );

                $ret[$id . $app] = $array;
                $child = self::getTree($value['id'], $id, $Level);
                // 后台管理界面只支持五层，超出的层级的不显示
                if ($child && $Level <= 5) {
                    $ret[$id . $app]['items'] = $child;
                }
            }
            return $ret;
        }

        return false;
    }

}