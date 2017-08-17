<?php
// +----------------------------------------------------------------------
// | 公共函数库，此函数会被自动加载
// +----------------------------------------------------------------------
// | Author: lipeng Date:2017-8-17
// +----------------------------------------------------------------------
use think\Db;

/* 配置信息修改 */
error_reporting(E_ERROR | E_PARSE );    // 屏蔽模版中未定义变量报错


/*-------------------------------函数库----------------------------------*/

/**
 * 根据参数，发送手机短信
 * @param $mobile int,接收短信的手机号码
 * @param $sms_tpl_code string, 短信模版编号，例如：SMS_33575026
 * @param $param array,发送的短信内容，如果是非空数组,内容是键值对的数组，里面的键应该和短信模版内容中的变量对应，顺序也对应
 * @param $send_scene string,短信发送场景，当此参数不为null时，绑定的参数数组，将从数据库中查找并和$param的内容做拼接，此情况下$param里面的是一维数组，只包含值,$sms_tpl_code为空
 * @return boolean
 */
function send_message($mobile,$sms_tpl_code='',$param=array(),$send_scene=null){
    if (empty($mobile)){
        return false;
    }
    if(!preg_match("/^1[34578]{1}\d{9}$/",$mobile)){
        return false;
    }
    if($send_scene==null && empty($sms_tpl_code)){
        return false;
    }
    $msg_config_info = Db::table('it_config')->where('name','in','sms_appkey,sms_secretKey,sms_product')->column('value');
    if(count($msg_config_info) != 3){
        return false;
    }
    $appkey =  $msg_config_info[0];
    $secret =  $msg_config_info[1];
    $sign_name =  $msg_config_info[2];
    if(empty($appkey) || empty($secret) || empty($sign_name)){
        return false;
    }
    vendor('alidayu.TopSdk');
    $client = new \TopClient;
    $client ->appkey = $appkey;
    $client ->secretKey = $secret;
    $req = new AlibabaAliqinFcSmsNumSendRequest;
    $req ->setExtend("");
    $req ->setSmsType("normal");
    $req ->setSmsFreeSignName($sign_name);
    // 绑定参数
    if($send_scene != null){    // 从数据库中查找并拼接
        $sms_template = Db::table('it_sms_template')->field('sms_tpl_code,sms_need_key')->where(['send_scene'=>$send_scene])->find();
        if(!empty($sms_template)){
            if (empty($sms_template['sms_tpl_code'])){
                return false;
            }
            $sms_tpl_code = trim($sms_template['sms_tpl_code']);    // 应用查询出来的模版ID
            if (!empty($sms_template['sms_need_key']) && !empty($param)){
                $sms_need_key = explode(',',$sms_template['sms_need_key']);
                if (count($sms_need_key) == count($param)){
                    $set_param = '';
                    foreach ($sms_need_key as $k=>$v){
                        $set_param .=$v.":'".$param[$k]."',";
                    }
                    $set_param = rtrim($set_param,',');
                    $req ->setSmsParam("{".$set_param."}");
                }
            }
        }else{
            return false;
        }
    }else{  // 直接获取参数
        if (!empty($param)){
            $set_param = '';
            foreach ($param as $k=>$v){
                $set_param .=$k.":'".$v."',";
            }
            $set_param = rtrim($set_param,',');
            $req ->setSmsParam("{".$set_param."}");
        }
    }
    $req ->setRecNum($mobile);
    $req ->setSmsTemplateCode($sms_tpl_code);
    $resp = $client ->execute($req);
    if(isset($resp->result->err_code) && $resp->result->err_code==0){
        return true;
    }else{
        return false;
    }
}



