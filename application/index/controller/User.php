<?php
/**
 * User: Lipeng
 * Date: 2017/6/16
 * Time: 17:28
 * 用户中心类
 */
namespace app\index\controller;

class User extends Common {
    /**
     *  用户中心默认页面
     */
    public function index(){
        return $this->fetch();
    }

    /**
     *  用户登陆模块，两次逻辑，展示和数据提交
     */
    public function login(){
        if ($this->request->isPost()){
            $data = input("post.");
            $user_name = $data['user_name'];
            $password = $data['password'];

            if (empty($user_name) || empty($password)){
                $this->error('输入有误，请重新输入！');
            }
            $db = db('users');
            $user_info = $db->field('user_id,password,last_ip,last_time')->where(['user_name'=>$user_name,'status'=>1])->find();
            if ($user_info){
                $password = $this->md5_password($password);
                if ($password !== $user_info['password']){
                    $this->error('用户密码错误！');
                }
                $request = $this->request;
                $ip = $request->ip();
                $time = time();
                $db->where('user_id',$user_info['user_id'])->update(['last_ip'=>$ip,'last_time'=>$time]);       // 更新登陆状态

                if ($user_info['last_ip'] != null){
                    session('last_ip',$user_info['last_ip']);
                }else{
                    session('last_ip',$ip);
                }

                if ($user_info['last_time'] != null){
                    session('last_time',$user_info['last_time']);
                }else{
                    session('last_time',$time);
                }
                session('user_id',$user_info['user_id']);
                session('user_name',$user_name);
                $this->success('登陆成功！',url('index'));
            }else{
                $this->error('用户名不存在！');
            }

        }else{
            return $this->fetch();
        }
    }

    /**
     *  用户注册模块，两次逻辑，展示和数据提交
     */
    public function register(){
        if ($this->request->isPost()){
            $data = input("post.");
            $user_name = $data['user_name'];
            $password = $data['password'];
            $password_confirm = $data['password_confirm'];

            /* 简单的验证 */
            if (empty($user_name) || empty($password) || empty($password_confirm)){
                $this->error('输入有误，请重新输入！');
            }
            if ($password != $password_confirm){
                $this->error('两次密码输入不一致！');
            }
            $db = db('users');

            /* 判断用户是否已经存在*/
            $exist_user = $db->where(['user_name'=>$user_name,'status'=>1])->count();
            if ($exist_user>0){
                $this->error('用户名已存在，请重新注册！');
            }

            $data['password'] = $this->md5_password($password);
            unset($data['password_confirm']);
            $request = $this->request;
            $data['reg_time'] = time();
            $data['reg_ip'] = $request->ip();
            $user_id = $db->insertGetId($data);
            if ($user_id){
               $this->error('注册成功！',url('login'));
            }else{
               $this->error('注册失败！');
            }
        }else{
            return $this->fetch();
        }
    }

    /**
     *  用户推出模块
     */
    public function logout(){
        $user_id = session('?user_id')? session('user_id'):false;
        if ($user_id){
            session('last_ip',null);
            session('last_time',null);
            session('user_id',null);
            session('user_name',null);
        }
        $this->success('退出成功！',url('login'));
    }

}