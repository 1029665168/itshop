<?php
/**
 * User: Lipeng
 * Date: 2017/6/16
 * Time: 17:28
 */
namespace app\admin\controller;

class Smstemplate extends Common {

    /* 短信模版列表 */
    public function index(){
        return $this->fetch();
    }

    /* 新增短信模版 */
    public function edit(){
        return $this->fetch();
    }


}