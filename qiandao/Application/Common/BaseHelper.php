<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/9 0009
 * Time: 下午 5:12
 */
namespace app\common;

use app\admin\model\Area;

class BaseHelper{
    /**
     * curl POST方式实现
     * @param $url
     * @param $postData
     * @return mixed
     * @author fei <xliang.fei@gmail.com>
     */
    public static function curlPost($url, $postData)
    {
        //初始化
        $curl = curl_init(); //用curl发送数据给api
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        $response = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        return $response;
    }

    /**
     * curl GET方式实现
     * @param $url
     * @return mixed
     * @author fei <xliang.fei@gmail.com>
     */
    public static function curlGet($url)
    {
        //初始化
        $curl = curl_init();
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        return $data;
    }

    //生成唯一ID
    public static function getUUID($prefix=''){
        $uuid = md5(uniqid(mt_rand(), true));
        return $prefix . $uuid;
    }

    public static function getUUID22(){
        $uuid = md5(uniqid(mt_rand(), true));
        return substr($uuid,mt_rand(0,10),22);
    }

    /**
     * 后台登录密码加密
     *
     * @param $password 要加密的密码
     * @param string $algo 要使用的哈希算法，例如："md5"，"sha256"，"haval160,4" 等
     * @param string $key 加密秘钥
     * @return string
     */
    public static function passwordEncrypt($password, $algo = 'ripemd256', $key = 'mxkj@2018'){
        $password = hash($algo, $key . $password);

        return $password;
    }

    /**
     * 把字符串打散为数组
     *
     * @param $delim
     * @param $string
     * @param bool $removeEmptyValues
     * @param int $limit
     * @return array
     */
    public static function trimExplode($delim, $string, $removeEmptyValues = false, $limit = 0)
    {
        $result = explode($delim, $string);
        if ($removeEmptyValues) {
            $temp = array();
            foreach ($result as $value) {
                if (trim($value) !== '') {
                    $temp[] = $value;
                }
            }
            $result = $temp;
        }
        if ($limit > 0 && count($result) > $limit) {
            $lastElements = array_splice($result, $limit - 1);
            $result[] = implode($delim, $lastElements);
        } elseif ($limit < 0) {
            $result = array_slice($result, 0, $limit);
        }
        $result = array_map('trim', $result);

        return $result;
    }

    /**
     * 得到树形菜单名
     *
     * @param object $model
     * @param array $where
     * @param array $orderBy
     * @param array $defaultOptions
     * @param int $id
     * @param string $titleField
     * @param string $parentField
     * @return array
     */
    public static function makeTreeOptions($model, $where = [], $orderBy = [], $defaultOptions = [], $id = 0, $titleField = 'name', $parentField = 'parentId')
    {
        $options = [];
        if (!empty($defaultOptions)) {
            $options = $defaultOptions;
        }
        $recursive = function ($id, $level) use (&$recursive, $model, $where, $orderBy, &$options, $titleField, $parentField) {
            $space = '';
            for ($i = 0; $i < $level; $i++) {
                $space .= '&nbsp;&nbsp;';
            }
            if ($level) $space .= '&nbsp;├&nbsp;';

            $model = $model::where(array_merge([$parentField => $id], $where));
            if (!empty($orderBy)) {
                foreach ($orderBy as $key => $val) {
                    $model = $model->order($key, $val);
                }
            }

            $results = $model->select();
            foreach ($results as $result) {
                $options[$result->id] = $space . $result->{$titleField};
                $recursive($result->id, $level + 1);
            }
        };
        $recursive($id, 0);

        return $options;
    }

    /**
     * 得到 select options
     *
     * @param $model
     * @param array $where
     * @param array $orderBy
     * @param array $defaultOptions
     * @param string $keyField
     * @param string $titleField
     * @param string $sep
     * @return array
     */
    public static function makeOptions($model, $where = [], $orderBy = [], $defaultOptions = [], $keyField = 'id', $titleField = 'name', $sep = '-')
    {
        $options = [];
        if (!empty($defaultOptions)) {
            $options = $defaultOptions;
        }
        $model = $model::where($where);
        if (!empty($orderBy)) {
            foreach ($orderBy as $key => $val) {
                $model = $model->order($key, $val);
            }
        }
        $results = $model->select();
        $tempTitle = [];
        if (is_array($titleField)) {
            $tempTitle = $titleField;
        } else {
            array_push($tempTitle, $titleField);
        }
        foreach ($results as $result) {
            $titleValue = [];
            foreach ($tempTitle as $field) {
                if ($result->{$field}) {
                    array_push($titleValue, $result->{$field});
                }
            }
            $options[$result->{$keyField}] = implode(' ' . $sep . ' ', $titleValue);
        }
        return $options;
    }

    /**
     * 得到树形菜单名视图
     *
     * @param object $model
     * @param array $where
     * @param array $orderBy
     * @param array $defaultOptions
     * @param int $id
     * @param string $titleField
     * @param string $parentField
     * @return string
     */
    public static function makeTreeViews($model, $where = [], $orderBy = [], $defaultOptions = [], $id = 0, $titleField = 'name', $parentField = 'parentId')
    {
        $nodeid = 'tree-node-' . md5(uniqid(microtime()));
        $output = '<div id="' . $nodeid . '"><ul>';
        if (!empty($defaultOptions)) {
            foreach ($defaultOptions as $key => $val) {
                $output .= '<li id="' . $id . '-' . $key . '" rel="' . $key . '">' . $val . '</li>';
            }
        }
        $recursive = function ($id, $level) use (&$recursive, $nodeid, $model, $where, $orderBy, &$output, $titleField, $parentField) {
            if ($level) {
                $output .= '<ul>';
            }
            $model = $model::where(array_merge([$parentField => $id], $where));
            if (!empty($orderBy)) {
                foreach ($orderBy as $key => $val) {
                    $model = $model->order($key, $val);
                }
            }
            $results = $model->select();
            foreach ($results as $result) {
                $iconClass = '';
                if($result->parentId == 0){
                    $iconClass = 'fa fa-cubes';
                }
                /*elseif($result->position == 4){
                    $iconClass = 'fa fa-file';
                }elseif($result->position == 0 || $result->position == 3){
                    $iconClass = 'fa fa-gear';
                }else{
                    $iconClass = 'fa fa-cube';
                }*/
                $output .= '<li id="' . $nodeid . '-' . $result->id . '" data-jstree=\'{"icon":"'.$iconClass.'"}\' rel="' . $result->id . '">' . $result->{$titleField};
                $recursive($result->id, $level + 1);
                $output .= '</li>';
            }
            if ($level) {
                $output .= '</ul>';
            }
        };
        $recursive($id, 0);
        $output .= '</ul></div>';

        return $output;
    }

}