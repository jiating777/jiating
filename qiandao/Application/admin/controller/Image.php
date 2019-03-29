<?php 

/**
 * 处理图片相关
 */

namespace app\admin\controller;

use think\Db;
use app\lib\Qiniu;
use think\Controller;
use \think\Request;
use app\common\BaseHelper;

class Image extends Controller{

	/**
	 * 获取七牛token
	 * @Author   jiating
	 * @DateTime 2018-04-20
	 * @return   json
	 */
	public function token() {
		$Qiniu = new Qiniu();
		return json(['uptoken'=>$Qiniu->getToken()]);
	}

	/**
     * 图片组件-获取所有图片-客户端分页
     * @Author   jiating
     * @DateTime 2018-05-16
     */
    public function getList(){
        $list = Db::table('image')->field('')->where('tenantId',session('TENANT_ID'))->order('created DESC')->select();
        $count = Db::table('image')->where('tenantId',session('TENANT_ID'))->count();
        $res = [
            'status' => '1',
            'data' => $list,
            'recordsFiltered' => $count,
            'recordsTotal' => $count
        ];
        return json($res);
    }

	/**
	 * 将上传的每一张图片记录到数据表中
	 * @Author   jiating
	 * @DateTime 2018-04-27
	 * imgUrl  图片地址
	 */
	public function record() {
		// $isVideo = (isset($_POST['isVideo']) && $_POST['isVideo']) ? $_POST['isVideo'] : 2;
		$controller = 'test';
        $id = BaseHelper::getUUID();
        $data = [
            'id' => $id,
            'imgUrl' => $_POST['imgUrl'],
            'controller' => $controller,
            'villageId' => session('ADMIN')['villageId'],
            'created' => time()
        ];

        $add = Db::table('image')->insert($data);
        $code = $add ? '1' : '2';
        return json(['image_id'=>$id,'code'=>$code]);
	}

	/**
	 * 图片删除-删除记录及七牛空间中的图片
	 * @Author   jiating
	 * @DateTime 2018-05-05
	 * id  图片记录id
	 * imgUrl  图片地址
	 * @return   json
	 */
	public function delete() {
		$id = $this->request->param('id');
		$url = $this->request->param('imgUrl');
		$key =  substr(strrchr($url, '/'), 1);
		// return $key;		
		$Qiniu = new Qiniu();
		$res = $Qiniu->delImg($key);  //删除图片
		if($res == NULL){
			//Db::table('image')->where('id',$id)->delete();  //删除总表记录
            Db::table('image')->where('imgUrl', $url)->delete();  //删除总表记录
            return json(['message'=>'success','code'=>'1']);
        }else{
            return json(['message'=>'删除失败','code'=>'2']);
        }
	}

}