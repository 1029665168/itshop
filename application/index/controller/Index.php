<?php
/**
 * User: Lipeng
 * Date: 2017/6/16
 * Time: 17:28
 */
namespace app\index\controller;

class Index extends Common {
    public function index(){
       $id = input('get.id');
       $this->redirect(url('edit',array('id'=>121)));
       dump($id);
    }

    public function edit(){
        $id = input('param.id');
        dump($id);
    }
}