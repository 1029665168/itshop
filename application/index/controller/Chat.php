<?php
/**
 * User: Lipeng
 * Date: 2017/6/16
 * Time: 17:28
 */
namespace app\index\controller;
use GatewayClient\Gateway;

class Chat extends Common {
    private $registerAddress = '127.0.0.1:4000';    //  设置GatewayWorker服务的Register服务ip和端口，用于内部通信

    public function index(){
        return $this->fetch();
    }


    /*
     * 用户连接上websock后，将客户端id与用户id和分组id绑定
     * */
    public function bind_server(){
        if($this->request->isPost()){
            $client_id = input('?post.client_id')?input('post.client_id'):'';
            $user_id = session('?user_id')? session('user_id'):false;
           if (!empty($client_id) && $user_id){
               //加载GatewayClient。关于GatewayClient参见本页面底部介绍
               vendor('GatewayWorker.Gateway');
               // 设置GatewayWorker服务的Register服务ip和端口，请根据实际情况改成实际值
               Gateway::$registerAddress = $this->registerAddress;
               $client_id = $_POST['client_id'];
               // 假设用户已经登录，用户uid和群组id在session中
               $user_id      =  'u'.$user_id;
               $group_id = 'g3';
               // client_id与uid绑定
               $res_bind =  Gateway::bindUid($client_id, $user_id);
               // 加入某个群组（可调用多次加入多个群组）
               $res_join = Gateway::joinGroup($client_id, $group_id);
               if ($res_bind && $res_join){
                   $data = array(
                       'user_id'=>$user_id,
                       'user_name'=>session('user_name')
                   );
                   $back_array = array(
                       'status'=>1,
                       'msg'=>'connect success!',
                       'data'=>$data
                   );
               }else{
                   $back_array = array(
                       'status'=>0,
                       'msg'=>'connect fail!'
                   );
               }
               return $back_array;
           }else{
               $back_array = array(
                   'status'=>0,
                   'msg'=>'Not login or no connect!'
               );
               return $back_array;
           }
        }
    }

    /* 发送消息 */
    public function send_message(){
        if ($this->request->isPost()){
            $message = input('?post.message')?input('post.message'):'';
            $user_id = session('?user_id')? session('user_id'):false;
            if (!empty($message) && $user_id){
                //加载GatewayClient。关于GatewayClient参见本页面底部介绍
                vendor('GatewayWorker.Gateway');
                // 设置GatewayWorker服务的Register服务ip和端口，请根据实际情况改成实际值
                Gateway::$registerAddress = $this->registerAddress;
                // 向任意uid的网站页面发送数据
                $user_id      =  'u'.$user_id;
                $group_id = 'g3';
                //Gateway::sendToUid($user_id, $message);
                // 向任意群组的网站页面发送数据
                $data = array(
                    'user_id'=>session('user_id'),
                    'user_name'=>session('user_name'),
                    'send_time'=>date('Y-m-d H:i:s',time()),
                    'message'=>$message
                );
                $data = json_encode($data);
                Gateway::sendToGroup($group_id, $data);
                $back_array = array(
                    'status'=>1,
                    'msg'=>'success!'
                );
                return $back_array;
            }else{
                $back_array = array(
                    'status'=>0,
                    'msg'=>'Not login or no message!'
                );
                return $back_array;
            }
        }
    }



}