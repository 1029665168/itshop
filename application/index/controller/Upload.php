<?php
/**
 * User: Lipeng
 * Date: 2017/6/16
 * Time: 17:28
 */
namespace app\index\controller;

use Plugins\uploads\Uploads;
require_once './../vendor/qiniu/Uploads.class.php';

class Upload extends Common {

    // 普通文件上传
    public function index(){
        if ($this->request->isPost()){
            $file = $_FILES['imgs'];
            $upload = new Uploads();
            //$file = 'C:\Users\Dream1024-PC4\Desktop\imgs\12.jpg';
            $res = $upload->qiniu_uploads($file,'u30');
            var_dump($res);
        }else{
            return $this->fetch();
        }
    }

    //七牛文件上上传，文件域方式
    public function file_upload(){
        if ($this->request->isPost()){

        }else{
            return $this->fetch();
        }
    }

    //七牛文件删除
    public function qiniu_file_delete(){
        $file = 'upload/u30/temp/201707/79_P_1493367306740.jpg';
        $upload = new Uploads();
        $res = $upload->qiniu_delete($file);
        if ($res){
            echo '删除成功！';
        }else{
            echo '删除失败！';
        }
    }

    public function bianma(){
        $str = "问下图片dsad.png";
        echo crc32($str);
    }
}