<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件


function get_id()
{
    $uuid = md5(uniqid(mt_rand(), true));
    return $uuid;
}

/**
 * 密码加密方法
 * @param string $pw 要加密的原始密码
 * @param string $authCode 加密字符串
 * @return string
 */
function md5_password($pw, $authCode = '')
{
    if (empty($authCode)) {
        $authCode = 'qMhsKe5rvotHLjcmvl';
    }
    $result = "###" . md5(md5($authCode . $pw));
    return $result;
}


/**
 * 密码比较方法,所有涉及密码比较的地方都用这个方法
 * @param string $password 要比较的密码
 * @param string $passwordInDb 数据库保存的已经加密过的密码
 * @return boolean 密码相同，返回true
 */
function md5_compare_password($password, $passwordInDb)
{
    return cmf_password($password) == $passwordInDb;

}


/**
 * 随机字符串生成
 * @param int $len 生成的字符串长度
 * @return string
 */
function random_string($len = 6)
{
    $chars = [
        "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
        "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
        "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
        "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
        "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
        "3", "4", "5", "6", "7", "8", "9"
    ];
    $charsLen = count($chars) - 1;
    shuffle($chars);    // 将数组打乱
    $output = "";
    for ($i = 0; $i < $len; $i++) {
        $output .= $chars[mt_rand(0, $charsLen)];
    }
    return $output;
}


/**
 * 后台API接口数据输出
 * @param $status  业务状态码
 * @param $message 信息提示
 * @param array $data 数据
 * @return \think\response\Json
 */
function show($status, $message, $data)
{
    $data = [
        'status' => $status,
        'message' => $message,
        'data' => $data
    ];
    return json($data);
}

/**
 * 后台API接口数据输出
 * @param $status  业务状态码
 * @param $message 信息提示
 * @param array $data 数据
 * @param int $total 数据总数量，即共多少条数据
 * @return \think\response\Json
 */
function showTotal($status, $message, $data, $total)
{
    $data = [
        'status' => $status,
        'message' => $message,
        'data' => $data,
        'total' => $total
    ];
    return json($data);
}

//致贫原因
function povertyreason()
{
    return ['1' => '因病', '2' => '因残', '3' => '因学', '4' => '缺土地', '5' => '缺水', '6' => '缺技术', '7' => '缺劳力', '8' => '缺资金', '9' => '交通条件落后', '10' => '因婚', '11' => '自身发展动力不足'];
}

function viewPovertyreason($data)
{
    $povertyreason = povertyreason();
    return $povertyreason[$data];
}

//帮扶计划
function aidingplan()
{
    return ['1' => '医疗帮扶', '2' => '种植帮扶', '3' => '养殖帮扶', '4' => '务工就业', '5' => '技术培训', '6' => '金融贷款', '7' => '政策帮扶', '8' => '土地流转', '9' => '产业帮扶', '10' => '捐助慰问', '11' => '加入电商', '12' => '教育帮扶', '13' => '创业帮扶', '14' => '基础设施帮扶'];
}

function viewAidingplan($data)
{
    $aidingplan = aidingplan();
    if (!strrchr($data, ',')) {
        $return[] = $aidingplan[$data];
    } else {
        $data = explode(',', $data);
        $return = [];
        foreach ($data as $v) {
            $return[] = $aidingplan[$v];
        }
        // $return = implode(',', $return);
    }
    return $return;
}

//贫困属性
function povertydegree()
{
    return ['1' => '一般贫困户', '2' => '低保贫困户', '3' => '五保贫困户'];
}

function viewPovertydegree($data)
{
    $povertydegree = povertydegree();
    return $povertydegree[$data];
}

//脱贫属性
function outpoverty()
{
    return ['1' => '未脱贫', '2' => '已脱贫', '3' => '返贫'];
}

function viewOutpoverty($data)
{
    $outpoverty = outpoverty();
    return $outpoverty[$data];
}


function getAreaName($data) {
    if(!$data) {
        return '';
    }
    return \app\admin\model\Area::where('id',$data)->value('name');
}

function getVillageName($data) {
    if(!$data) {
        return '';
    }
    return \app\admin\model\Villages::where('id',$data)->value('name');
}


/**
 * 获取客户端IP
 */
function get_IP()
{
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
        $ip = getenv("HTTP_CLIENT_IP");
    } else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    } else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")){
        $ip = $_SERVER['REMOTE_ADDR'];
    } else {
        $ip = "unknown";
    }

    return  $ip;
}

/**
 * 返回带参数的URL
 *
 * @param $url
 * @return string
 */
function fullUrl($url)
{
    $uriArr = explode('/', $url);
    $params = [];
    if (count($uriArr) >= 3) {
        foreach ($uriArr as $key => $param) {
            if (in_array($key, [0, 1, 2])) {
                continue;
            }
            if ($key % 2 != 0 && isset($uriArr[$key + 1])) {
                $params[$param] = $uriArr[$key + 1];
            }

        }

        return url("$uriArr[0]/$uriArr[1]/$uriArr[2]", $params);
    }

    return url($url);
}

/**
 * 把三维数组转化成二维数组
 */
function array_merge_rec($array)
{
    // 定义一个新的数组
    $newArray = array();
    // 遍历当前数组的所有元素
    foreach ($array as $item) {
        if (is_array($item)) {
            // 如果当前数组元素还是数组的话，就递归调用方法进行合并
            array_merge_rec($item);
            // 将得到的一维数组和当前新数组合并
            $newArray = array_merge($newArray, $item);
        } else {
            // 如果当前元素不是数组，就添加元素到新数组中
            $newArray[] = $item;
        }
    }
    // 修改引用传递进来的数组参数值
    $array = $newArray;

    return $array;
}

/**
 * 二维数组根据某个元素去重
 */
function array_unset($arr, $key){
    // 建立一个目标数组
    $res = array();
    foreach ($arr as $value) {
        // 查看有没有重复项
        if(isset($res[$value[$key]])){
            //有：销毁
            unset($value[$key]);
        }else{
            $res[$value[$key]] = $value;
        }
    }

    return $res;
}


/**
 * 获取订单状态
 * @return array
 */
function get_order_status()
{
    // TODO
    return [
        '1' => '待付款',
        '2' => '待发货',
        '3' => '待收货',
        '4' => '已完成',
        '5' => '已失效',
        '6' => '售后',
        '7' => '已消费',
        '8' => '未消费',
        '9' => '同意售后退款',
        '10' => '拒绝售后退款',
    ];
}

// 显示订单状态
function show_order_status($id)
{
    $status = get_order_status();

    return $status[$id];
}

/**
 * 获取状态 （通用）
 * @return array
 */
function get_status()
{
    return [
        '1' => '是',
        '2' => '否'
    ];
}

// 显示状态 （通用）
function show_status($id)
{
    $status = get_status();

    return $status[$id];
}

/**
 * 性别
 * @return array
 */
function get_gender()
{
    return [
        '0' => '未知',
        '1' => '男',
        '2' => '女'
    ];
}

// 显示性别
function show_gender($genderId)
{
    $genders = get_gender();

    return $genders[$genderId];
}

/**
 * 文化程度
 * @return array
 */
function get_education()
{
    return [
        '1' => '研究生',
        '2' => '大学本科',
        '3' => '大学专科',
        '4' => '中专',
        '5' => '高中',
        '6' => '初中',
        '7' => '小学',
        '8' => '文盲',
        '9' => '半文盲'
    ];
}

// 显示文化程度
function show_education($id)
{
    $educations = get_education();

    return $educations[$id];
}

/**
 * 健康状况
 * @return array
 */
function get_health()
{
    return [
        '1' => '健康',
        '2' => '一般',
        '3' => '较差',
        '4' => '长期慢性疾病',
        '5' => '严重疾病',
        '6' => '身体伤残'
    ];
}

// 显示健康状况
function show_health($id)
{
    $healths = get_health();

    return $healths[$id];
}

/**
 * 村资源类型
 * @return array
 */
function get_villageresources_category()
{
    return [
        '1' => '办公楼',
        '2' => '仓库',
        '3' => '厂房',
        '4' => '商铺',
        '5' => '耕地',
        '6' => '园地',
        '7' => '林地',
        '8' => '水面',
        '9' => '牧场',
        '10' => '矿场',
        '11' => '建设用地',
        '12' => '其他'
    ];
}

// 显示村资源类型
function show_villageresources_category($id)
{
    $category = get_villageresources_category();

    return $category[$id];
}

/**
 * 村资源状态
 * @return array
 */
function get_villageresources_status()
{
    return [
        '1' => '待发包',
        '2' => '待出让',
        '3' => '待转让',
        '4' => '待租赁',
        '5' => '已发包',
        '6' => '已出让',
        '7' => '已转让',
        '8' => '已租赁'
    ];
}

// 显示村资源状态
function show_villageresources_status($id)
{
    $status = get_villageresources_status();

    return $status[$id];
}

/**
 * 产品单位
 * @return array
 */
function get_product_unit()
{
    return [
        '1' => '斤',
        '2' => '公斤',
        '3' => '两',
        '4' => 'g',
        '5' => 'kg',
        '6' => '只',
        '7' => '个',
        '8' => '片',
        '9' => '枚',
        '10' => '张',
        '11' => '粒',
        '12' => '瓶',
        '13' => '箱',
        '14' => '盒',
        '15' => '包',
        '16' => '罐',
        '17' => '袋',
        '18' => '坛',
        '19' => '桶',
        '20' => '升',
        '21' => '毫升',
        '22' => 'cm',
        '23' => 'mm',
    ];
}

// 显示产品单位
function show_product_unit($id)
{
    $units = get_product_unit();

    return $units[$id];
}

/**
 * 资源价格-单位
 * @return array
 */
function get_villageresources_price_unit()
{
    return [
        '1' => '年',
        '2' => '季度',
        '3' => '月',
    ];
}

// 显示资源价格-单位
function show_villageresources_price_unit($id)
{
    $units = get_villageresources_price_unit();

    return $units[$id];
}

/**
 * 资源数量-单位
 * @return array
 */
function get_villageresources_number_unit()
{
    return [
        '1' => '公顷',
        '2' => '亩',
        '3' => 'm²',
    ];
}

// 显示资源数量-单位
function show_villageresources_number_unit($id)
{
    $units = get_villageresources_number_unit();

    return $units[$id];
}

/**
 * 页面位置
 * @return array
 */
function get_pagesposition()
{
    return [
        '1' => '首页',
        //'2' => '乡村头条',
        '3' => '全部村子',
        '4' => '村里事',
        '5' => '智慧党建',
        '6' => '精准扶贫',
        '7' => '一村一品',
        //'8' => '社区',
        '9' => '农事服务',
    ];
}

// 显示页面位置
function show_pagesposition($id)
{
    $positions = get_pagesposition();

    return $positions[$id];
}

/**
 * 链接类型
 * @return array
 */
function get_link_type()
{
    return [
        '1' => '首页',
        '2' => '乡村头条',
        '3' => '全部村子',
        '4' => '村里事',
        '5' => '智慧党建',
        '6' => '精准扶贫',
        '7' => '一村一品',
        '8' => '社区',
        '9' => '农事服务',
    ];
}

// 显示链接类型
function show_link_type($id)
{
    $linkType = get_link_type();

    return $linkType[$id];
}

/**
 * 课程分类
 * @return array
 */
function get_microclass_category()
{
    return [
        '1' => '党建党章',
        '2' => '习近平讲话',
        '3' => '十九大精神',
        '4' => '三会一课',
        '5' => '治党治国',
        '6' => '党史',
    ];
}

// 显示课程分类
function show_microclass_category($id)
{
    $category = get_microclass_category();

    return $category[$id];
}