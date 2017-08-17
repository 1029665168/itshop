<?php
/**
 * User: Lipeng
 * Date: 2017/6/16
 * Time: 17:28
 */
namespace app\admin\controller;

use think\captcha\Captcha;

class Login extends Common {

    /*用户登录页面显示与验证*/
    public function index(){
        if ($this->request->isPost()){

            // 检测验证码
            if (!input('?post.vertify')){
                $back_data = array(
                    'status'=>0,
                    'msg'=>'未输入验证码'
                );
                exit(json_encode($back_data));
            }
            $code = trim(input('post.vertify'));
            $Captcha = new Captcha();
            $check_res = $Captcha->check($code,'admin_login');
            if($check_res == false){
                $back_data = array(
                    'status'=>0,
                    'msg'=>'验证码错误'
                );
                exit(json_encode($back_data));
            }

            // 检测用户名和密码
            if (!input('?post.username') || !input('post.password')){
                $back_data = array(
                    'status'=>0,
                    'msg'=>'未输入用户名或密码'
                );
                exit(json_encode($back_data));
            }
            $username = trim(input('post.username'));
            $password = trim(input('post.password'));
            if ($username=='' || $password==''){
                $back_data = array(
                    'status'=>0,
                    'msg'=>'用户名或密码为空'
                );
                exit(json_encode($back_data));
            }
            $password = $this->md5_password($password);
            $admin_info  = db('manager')->field('manager_id,manager_name,last_login,last_ip')->where(['manager_name'=>$username,'password'=>$password])->find();
            if ($admin_info != null){
                session('manager_id',$admin_info['manager_id']);
                session('manager_name',$admin_info['manager_name']);
                session('manager_last_login_time',$admin_info['last_login']);
                session('manager_last_login_ip',$admin_info['last_ip']);
                $request = request();
                $ip = $request->ip();
                db('manager')->where('manager_id',$admin_info['manager_id'])->update(['last_login'=>time(),'last_ip'=>$ip]);
                $back_data = array(
                    'status'=>1,
                    'msg'=>'验证通过',
                    'url'=>url('index/index')
                );
                exit(json_encode($back_data));
            }else{
                $back_data = array(
                    'status'=>0,
                    'msg'=>'用户名或密码错误'
                );
                exit(json_encode($back_data));
            }
        }else{
            if (!empty(session('manager_id'))){
                $this->error('您已登录，不能重复登录！');
            }else{
                return $this->fetch('index');
            }
        }
    }

    /*验证码生成*/
    public function vertify(){
        $config = array(
            'fontSize' => 30,
            'length' => 4,
            'useCurve' => true,
            'useNoise' => false,
            'reset' => false
        );
        $Captcha = new Captcha($config);
        return $Captcha->entry("admin_login");
    }

    /* 后台退出 */
    public function logout(){
        if (session('?manager_id')){
            session('manager_id',null);
            session('manager_name',null);
            session('manager_last_login_time',null);
            session('manager_last_login_ip',null);
        }
        $this->redirect(url('login/index'));
    }

}