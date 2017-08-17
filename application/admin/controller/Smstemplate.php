<?php
/**
 * User: Lipeng
 * Date: 2017/6/16
 * Time: 17:28
 */
namespace app\admin\controller;

class Smstemplate extends Common {

    /* 阿里大于验证码模版参数 */
    private  $static_templete =array(
        0=>array(

        ),
    );

    /* 短信模版列表 */
    public function index(){
        return $this->fetch();
    }

    /* 新增短信模版 */
    public function edit(){
        return $this->fetch();
    }

    public function send_msg(){
        $param = array(
            'code'=>rand(1000,9999),
            'product'=>'西子丝纺'
        );
        $param1 = array(1212,'西子丝纺1');
        $res = send_message('15869168657','',$param1,'5');
        if($res){
            echo 'ss';
        }else{
            echo 'fail';
        }
    }


}