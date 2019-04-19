<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/15 0015
 * Time: 17:30
 */

namespace app\api\controller;


use app\common\BaseHelper;

class Weather extends BaseController
{
    public function getWeather()
    {
        $weather = json_decode(BaseHelper::curlGet("http://t.weather.sojson.com/api/weather/city/101230101"));
        if (empty($weather)) {
            return show(config('status.ERROR_STATUS'), self::NOT_DATA, '未查到当地天气');
        } else {
            $temp = $weather->data->forecast[0];
            return show(config('status.SUCCESS_STATUS'), self::MSG_SUCCESS, $temp);
        }

    }
}