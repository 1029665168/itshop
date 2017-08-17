<?php
/**
 * User: Lipeng
 * Date: 2017/6/16
 * Time: 17:28
 */
namespace app\admin\controller;

class Email extends Common {

    /* 后台发送邮件测试 */
    public function email_test(){
        $param = input('post.');
        $subject = '测试邮件';
        $content = '您好！这是一封检测邮件服务器设置的测试邮件。收到此邮件，意味着您的邮件服务器设置正确！您可以进行其它邮件发送的操作了！';
        if($this->send_email_test($param,$subject,$content,'西子丝纺')){
            exit(json_encode(1));
        }else{
            exit(json_encode(0));
        }
    }

    /**
     * 邮件发送，正式用
     * @param $to    接收人
     * @param string $subject   邮件标题
     * @param string $content   邮件内容(html模板渲染后的内容)
     * @throws Exception
     * @throws phpmailerException
     * @return  boolean
     */
    function send_email($to,$subject='',$content=''){
        vendor('phpmailer.PHPMailerAutoload'); // require_once vendor/phpmailer/PHPMailerAutoload.php';
        $mail = new \PHPMailer;
        $smtp_config = $this->get_config_info('smtp');  // 获取邮箱配置
        $mail->CharSet  = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
        $mail->isSMTP();
        //Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $mail->SMTPDebug = 0;
        //调试输出格式
        //$mail->Debugoutput = 'html';
        //smtp服务器
        if($smtp_config['smtp_port'] == 465){
            $mail->Host = 'ssl://'.$smtp_config['smtp_server'];     //加密协议
        }else{
            $mail->Host = $smtp_config['smtp_server'];
        }
        //端口 - likely to be 25, 465 or 587
        $mail->Port = $smtp_config['smtp_port'];

        // 使用安全协议
        if($mail->Port === 465){
            $mail->SMTPSecure = 'ssl';
        }

        //Whether to use SMTP authentication
        $mail->SMTPAuth = true;
        //用户名
        $mail->Username = $smtp_config['smtp_user'];
        //密码
        $mail->Password = $smtp_config['smtp_pwd'];
        //Set who the message is to be sent from
        $mail->setFrom($smtp_config['smtp_user']);
        //回复地址
        //$mail->addReplyTo('replyto@example.com', 'First Last');
        //接收邮件方
        if(is_array($to)){
            foreach ($to as $v){
                $mail->addAddress($v);
            }
        }else{
            $mail->addAddress($to);
        }

        $mail->isHTML(true);// send as HTML
        //标题
        $mail->Subject = $subject;
        //HTML内容转换
        $mail->msgHTML($content);
        //Replace the plain text body with one created manually
        //$mail->AltBody = 'This is a plain-text message body';
        //添加附件
        //$mail->addAttachment('images/phpmailer_mini.png');
        //send the message, check for errors

        return $mail->send();
    }

    /**
     * 根据邮箱配置信息类型查询信息
     * @param $config_type ,配置信息类型
     * @return  array ,一维数组
     */
    private function get_config_info($config_type=''){
        $config_info = db('config')->field('name,value')->where('config_type', $config_type)->select();
        $config = array();
        if (!empty($config_info)){
            foreach ($config_info as $key=>$value){
                $config[$value['name']] = $value['value'];
            }
        }
        return $config;
    }

    /**
     * 邮件发送，后台测试专用
     * @param $param    ,js 提交过来的 stmp 数据
     * @param string $subject   邮件标题
     * @param string $content   邮件内容(html模板渲染后的内容)
     * @throws Exception
     * @throws phpmailerException
     * @return  boolean
     */
    function send_email_test($param,$subject='',$content='',$from_name){
        if (!empty($param)){
            vendor('phpmailer.PHPMailerAutoload'); // require_once vendor/phpmailer/PHPMailerAutoload.php';
            $mail = new \PHPMailer;
            $smtp_config = $param;  // 获取邮箱配置
            $mail->CharSet  = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
            $mail->isSMTP();
            //Enable SMTP debugging
            // 0 = off (for production use)
            // 1 = client messages
            // 2 = client and server messages
            $mail->SMTPDebug = 0;
            //调试输出格式
            //$mail->Debugoutput = 'html';

            //smtp服务器
            if($smtp_config['smtp_port'] == 465){
                $mail->Host = 'ssl://'.$smtp_config['smtp_server'];     //加密协议
            }else{
                $mail->Host = $smtp_config['smtp_server'];
            }

            //端口 - likely to be 25, 465 or 587
            $mail->Port = $smtp_config['smtp_port'];

            // 使用安全协议
            if($mail->Port === 465){
                $mail->SMTPSecure = 'ssl';
            }

            //Whether to use SMTP authentication
            $mail->SMTPAuth = true;
            //用户名
            $mail->Username = $smtp_config['smtp_user'];
            //密码
            $mail->Password = $smtp_config['smtp_pwd'];
            //Set who the message is to be sent from
            $mail->setFrom($smtp_config['smtp_user'],$from_name);
            $mail->FromName =  $from_name;  // 发件人
            //回复地址
            //$mail->addReplyTo('replyto@example.com', 'First Last');
            //接收邮件方
            $mail->addAddress($smtp_config['test_email']);

            $mail->isHTML(true);// send as HTML
            //标题
            $mail->Subject = $subject;
            //HTML内容转换
            $mail->msgHTML($content);
            return $mail->send();
        }
    }


}