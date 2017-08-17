<?php
/**
 * User: Lipeng
 * Date: 2017/6/16
 * Time: 17:28
 */
namespace app\index\controller;

class Bdupload extends Common {

    public function index(){
        return $this->fetch();
    }

    public function baidu_upload(){
        if ($this->request->isPost()) {
            $this->web_uploader('file');
        }else{
            return $this->fetch('baidu_upload');
        }
    }





}