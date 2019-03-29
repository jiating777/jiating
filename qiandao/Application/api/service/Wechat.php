<?php

/**
 * 微信小程序用户相关接口
 */

namespace app\api\service;
use think\Cache;

class Wechat {

    public function create($params) {
        $wechat = $this->get_open_id($params);

        if(empty($wechat['openid']) || empty($wechat['session_key'])){
            return json(false);
        }
        $openid = $wechat['openid'];
        $session_key = $wechat['session_key'];

        $key = $this->rd_session();
        Cache::set($key,$session_key.$openid,1800);
        return $key;
    }

	public function get_open_id($params) {
        $code = trim($params['code']);
        if(!$code){
        	return array('code'=>'300001','msg'=>'code fail');
        }

        $grand_type = 'authorization_code';

        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$params['appId'].'&secret='.$params['appSecret'].'&js_code='.$code.'&grant_type='.$grand_type;

        //模拟POST
        $output = $this->curl_post($url);

        if(isset($output['openid'])) {  
            return $output;
        } else {
            return false;
        }

    }

    private function curl_post($url) {
        //模拟POST
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array());
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  //跳过ssl检查
        $output =  curl_exec($ch);
        $info = curl_getinfo($ch);
        if($output === false) {
            $output = "No cURL data returned for $url [". $info['http_code']. "]";
            if (curl_error($ch)){
                $output .= "\n". curl_error($ch);
              }
        }
        curl_close($ch);
        return json_decode($output,true);
    }

    function devurandom_rand($min = 0, $max = 0x7FFFFFFF) {
        $diff = $max - $min;
        if ($diff < 0 || $diff > 0x7FFFFFFF) {
            throw new RuntimeException("Bad range");
        }
        $bytes = mcrypt_create_iv(8, MCRYPT_DEV_URANDOM);
        if ($bytes === false || strlen($bytes) != 8) {
            throw new RuntimeException("Unable to get 8 bytes");
        }
        $ary = unpack("Nint", $bytes);
        $val = $ary['int'] & 0x7FFFFFFF;   // 32-bit safe
        $fp = (float) $val / 2147483647.0; // convert to [0,1]
        return round($fp * $diff) + $min;
    }

    function rd_session() {
        $session3rd  = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;
        for($i=0;$i<16;$i++){
            $session3rd .=$strPol[rand(0,$max)];
        }
        return $session3rd;

    }



}