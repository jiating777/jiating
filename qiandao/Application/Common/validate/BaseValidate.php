<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/4/19 0019
 * Time: 下午 4:02
 */
namespace app\common\validate;

use think\Validate;
use app\lib\exception\ParameterException;
use think\Request;

/**
 * 验证器基类
 * Class BaseValidate
 * @package app\common\validate
 */
class BaseValidate extends Validate{


    public function goCheck()
    {
        // 获取http传入的参数
        // 对这些参数做检验
        $request = Request::instance();
        $params = $request->param();

        $result = $this->batch()
            ->check($params);
        if (!$result)
        {
            $e = new ParameterException(
                [
                    'msg' => $this->error,
                ]);
            throw $e;
        }
        else
        {
            return true;
        }
    }

    /**
     * 验证是正整数
     * @param $value
     * @param string $rule
     * @param string $data
     * @param string $field
     * @return bool
     */
    protected function isPositiveInteger($value, $rule = '', $data = '', $field = '')
    {
        if (is_numeric($value) && is_int($value + 0) && ($value + 0) > 0)
        {
            return true;
        }
        else
        {
            return false;
            //            return $field.'必须是正整数';
        }
    }

    protected function isMobile($value)
    {
        $rule = '^1(3|4|5|6|7|8)[0-9]\d{8}$^';
        $result = preg_match($rule, $value);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    protected function isphone($value){
        $isMob='^1(3|4|5|6|7|8)[0-9]\d{8}$^';

        $isTel='/^([0-9]{3,4}-)?[0-9]{7,8}$/';
        if(preg_match($isMob,$value) || preg_match($isTel,$value)){
            return true;
        }else{
            return false;
        }
    }

    protected function isCurrency($value){

        if($value<1 || $value>2000000){
            return false;
        }
        if (preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $value)) {
            return true;
        }else{
            return false;
        }
    }

    protected function checkmoney($value){
        if($value<=0){
            return false;
        }
        if (preg_match('/^[0-9]+(.[0-9]{1,2})?$/', $value)) {
            return true;
        }else{
            return false;
        }
    }

    /**
     * 验证不为空
     * @param $value
     * @param string $rule
     * @param string $data
     * @param string $field
     * @return bool
     */
    protected function isNotEmpty($value, $rule = '', $data = '', $field = ''){
        if(empty($value)){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 根据验证器规则过滤客户端提交的数据
     * @param $arrays
     * @return array
     * @throws ParameterException
     */
    public function getDataByRule($arrays)
    {
        if (array_key_exists('user_id', $arrays) || array_key_exists('uid', $arrays))
        {
            // 不允许包含user_id或者uid，防止恶意覆盖user_id外键
            throw new ParameterException(
                [
                    'msg' => '参数中包含有非法的参数名user_id或者uid'
                ]);
        }
        $newArray = [];

        foreach ($this->rule as $key => $value)
        {
            $newArray[$key] = $arrays[$key];
        }
        return $newArray;
    }
}