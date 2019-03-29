<?php 

/**
 * 七牛存储
 * @Author      jiating
 * @DateTime    2018-04-17
 */

namespace app\lib;

require VENDOR_PATH.'/qiniu/autoload.php';
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

use app\common\BaseHelper;

class Qiniu{

	public function __construct() {
		// 用于签名的公钥和私钥
		$this->accessKey = '_WXEvsGC2_eokbBnAF2PbItB8S_20-hB8wCgsTIj';
		$this->secretKey = 'uszpUPK6z_5OuVkkcdS1ilg68bqPqSkXVXBlIsdB';
		$this->bucket = "miaodao";
        $this->Qiniu_url="http://img.1miaodao.cn/";
	  
		$this->auth = new Auth($this->accessKey, $this->secretKey);  // 初始化签权对象
	}


	public function getToken() {
		$expires = 86400;
		$policy = null;
		return $this->auth->uploadToken($this->bucket,null, $expires, $policy, true);
	}

    public function postDoupload($file){
        $token = $this->getToken();
        $uploadManager = new UploadManager();
        $name = $file['name'];
        $extStr = explode('.',$file['name']);
        $ext = $extStr[0];
        $name = BaseHelper::getUUID('img').'.'.$extStr[count($extStr)-1];  //重命名

        $filePath = $file['tmp_name'];
        $type = $file['type'];
        list($ret,$err) = $uploadManager->putFile($token,$name,$filePath,null,$type,false);
        if($err) { //上传失败
            return ['status'=>'fail','message'=>$err,'key'=>''];
        } else { //成功
            return ['status'=>'success','message'=>'上传成功','key'=>$ret['key']];
        }
    }

    /**
     * 图片删除
     * @Author   jiating
     * @DateTime 2018-05-05
     * $key  图片名称
     * @return   json
     */
    public function delImg($key){
        $auth = new Auth($this->accessKey, $this->secretKey);
        $bucketMgr = new BucketManager($auth);
        $err = $bucketMgr->delete($this->bucket, $key);
        return $err;
    }



}