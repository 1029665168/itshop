<?php
/**
 * User: Lipeng
 * Date: 2017/6/16
 * Time: 17:28
 */
namespace app\admin\controller;

class Upload extends Common {

    /* 文件上传页面展示和提交 */
    public function upload(){
        if ($this->request->isPost()) {
            $this->web_uploader('file_tmp');
        }else{
            //给上传控件传递参数
            $param = input('param.');
            $number = isset($param['num'])?intval($param['num']):1;
            $callback =  isset($param['callback'])?$param['callback']:'';
            $info = array(
                'number'=> $number,
                'size' => '4',
                'type' =>'jpg,png,gif,jpeg',
                'callback'=>$callback
            );
            return $this->fetch('',['info'=>$info]);
        }
    }

    /*
              删除上传的图片
     */
    public function delupload(){
        $action = I('action');
        $filename= I('filename');
        $filename= str_replace('../','',$filename);
        $filename= trim($filename,'.');
        $filename= trim($filename,'/');
        if($action=='del' && !empty($filename) && file_exists($filename)){
            $size = getimagesize($filename);
            $filetype = explode('/',$size['mime']);
            if($filetype[0]!='image'){
                return false;
                exit;
            }
            unlink($filename);
            exit;
        }
    }

}