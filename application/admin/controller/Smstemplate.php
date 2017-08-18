<?php
/**
 * User: Lipeng
 * Date: 2017/6/16
 * Time: 17:28
 */
namespace app\admin\controller;

class Smstemplate extends Common {

    /* 阿里大于验证码模版参数，仅用于后台添加修改时 */
    private  $static_template =array(
        0=>array(
            'sms_tpl_name'=>'用户注册验证码',
            'sms_tpl_code'=>'SMS_33575022',
            'send_scene'=>'user_register',
            'sms_need_key'=>'code,product',
            'sms_tpl_content'=>'验证码${code}，您正在注册成为${product}用户，感谢您的支持！'
        ),
        1=>array(
            'sms_tpl_name'=>'修改密码验证码',
            'sms_tpl_code'=>'SMS_33575020',
            'send_scene'=>'user_modify_pwd',
            'sms_need_key'=>'code,product',
            'sms_tpl_content'=>'验证码${code}，您正在尝试修改${product}登录密码，请妥善保管账户信息。'
        ),
    );

    /* 短信模版列表 */
    public function index(){
        $info = db('sms_template')->select();
        return $this->fetch('',['info'=>$info]);
    }

    /* 新增短信模版 */
    public function edit(){
        if ($this->request->isPost()){
            $data = input('post.');
            if($data['tpl_id'] != ''){  //修改
                $map['send_scene'] = $data['send_scene'];
                $map['tpl_id'] = ['<>',$data['tpl_id']];
                $old_info = db('sms_template')->where($map)->find();
                if($old_info){
                    $this->error('信息已经存在！');
                }
                $res = db('sms_template')->where('tpl_id',$data['tpl_id'])->update($data);
                if($res){
                    $this->success('更新成功！');
                }else{
                    $this->error('更新失败！');
                }
            }else{  //新增
                unset($data['tpl_id']);
                $data['create_time'] = time();
                $old_info = db('sms_template')->where(['send_scene'=>$data['send_scene']])->find();
                if($old_info){
                    $this->error('信息已经存在！');
                }
                $res = db('sms_template')->insert($data);
                if($res){
                    $this->success('添加成功！');
                }else{
                    $this->error('添加失败！');
                }
            }
        }else{
            $tpl_id = input('?param.tpl_id')?intval(input('param.tpl_id')):0;
            if($tpl_id>0){
                $info = db('sms_template')->where('tpl_id',$tpl_id)->find();
            }else{
                $info=array();
            }
            $template = $this->static_template;
            return $this->fetch('',['template'=>$template,'info'=>$info]);
        }
    }

    public function send_msg(){
        $param = array(
            'code'=>rand(1000,9999),
            'product'=>'西子丝纺'
        );
        $param1 = array(1212,'西子丝纺');
        $res = send_message('18263638875','',$param1,'user_modify_pwd');
        if($res){
            echo 'ss';
        }else{
            echo 'fail';
        }
    }


}