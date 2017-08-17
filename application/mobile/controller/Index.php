<?php
/**
 * User: Lipeng
 * Date: 2017/6/16
 * Time: 17:28
 */
namespace app\mobile\controller;

class Index extends Common {
    public function index(){
     return $this->fetch('index');
    }

    public function edit(){
        $id = input('param.id');
        dump($id);
    }
}