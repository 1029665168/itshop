<?php
/**
 * User: Lipeng
 * Date: 2017/6/16
 * Time: 17:28
 */
namespace app\index\controller;

class Jqtest extends Common {

    //目录
    public function index(){
        return $this->fetch();
    }

    public function jquery_syntax(){
        return $this->fetch();
    }
}