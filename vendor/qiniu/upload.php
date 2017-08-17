<?php
require_once __DIR__ . '/autoload.php';

// 引入鉴权类
use Qiniu\Auth;

// 引入上传类
use Qiniu\Storage\UploadManager;

$config = array(
    'accessKey'			=> 'DpVpbEr0BNVvjCaQ_JnqjJj7F_4XUa8ffvwDKsAA', 		//oss 访问密钥
    'secrectKey'		=> '4uW-ID_7ZMjH1XWQAmjLKhGhi92caD-E9fNqOeO8', 		//oss 安全密钥
    'bucket'            =>  'xizisifang',
    //'domain'            =>  'http://os7bf3nmw.bkt.clouddn.com/',
    'domain'            =>  'http://image.xizisifang.com/',
);
/**
 **
 * 上传文件
 * @param 文件信息数组 $files ，通常是 $_FILES数组
 */
function oss_upload($files='',$dir='') {
    if('' === $files){
        $files  =   $_FILES;
    }
    if(empty($files)){
        echo '没有上传文件';
        return false;
    }
    // 对上传文件数组信息处理
    $files   =  dealFiles($files);

    foreach ($files as $key => $file) {
        $file['name']  = strip_tags($file['name']);
        if(!isset($file['key']))   $file['key']    =   $key;
        /* 通过扩展获取文件类型，可解决FLASH上传$FILES数组返回文件类型错误的问题 */
        if(isset($finfo)){
            $file['type']   =   finfo_file ( $finfo ,  $file['tmp_name'] );
        }

        /* 获取上传文件后缀，允许上传无后缀文件 */
        $file['ext']    =   pathinfo($file['name'], PATHINFO_EXTENSION);

        /* 文件上传检测 */
        if (!check($file)){
            continue;
        }

        /* 获取文件hash */
        $file['md5']  = md5_file($file['tmp_name']);
        $file['sha1'] = sha1_file($file['tmp_name']);

        /* 生成保存文件名 */

        $file['savename'] = time().rand(10000,99999).'.'.$file['ext'];
        /* 检测并创建子目录 */
        $time_data = date('Ym',time());
        if ($dir != ''){
            $savepath = $dir.'/'.$time_data.'/';
        }else{
            $savepath = 'temp/'.$time_data.'/';
        }
        $file['savepath'] = $savepath;
        /* 对图像文件进行严格检测 */
        $ext = strtolower($file['ext']);
        if(in_array($ext, array('gif','jpg','jpeg','bmp','png','swf'))) {
            $imginfo = getimagesize($file['tmp_name']);
            if(empty($imginfo) || ($ext == 'gif' && empty($imginfo['bits']))){
                echo  '非法图像文件！';
                continue;
            }
        }

        /* 保存文件 并记录保存成功的文件 */
        if (save($file,true)) {
            unset($file['error'], $file['tmp_name']);
            return $file;
        } else {
            echo '上传失败';
        }
    }
    if(isset($finfo)){
        finfo_close($finfo);
    }
    return empty($info) ? false : $info;
}

/**
 * 保存指定文件
 * @param  array   $file    保存的文件信息
 * @param  boolean $replace 同名文件是否覆盖
 * @return boolean          保存状态，true-成功，false-失败
 */
function save(&$file) {
    global $config;

    // 构建鉴权对象
    $auth = new Auth($config['accessKey'], $config['secrectKey']);

    // 生成上传 Token
    $token = $auth->uploadToken($config['bucket']);

    // 初始化 UploadManager 对象并进行文件的上传。
    $uploadMgr = new UploadManager();

    // 调用 UploadManager 的 putFile 方法进行文件的上传。
    $res = $uploadMgr->putFile($token, $file['savepath'].$file['savename'], $file['tmp_name']); //保存文件到远程服务器

    //保存文件到本地
    /*$savepath = './images/'.$file['savepath'];
    if (!file_exists($savepath)){
        @mkdir($savepath,0777,true);
    }
    move_uploaded_file($file['tmp_name'],$savepath.$file['savename']);*/
    if ($res[0] != null){
        $file['url'] =  $config['domain'].$res[0]['key'];
        $file['domain'] = $config['domain'];
        $file['bucket'] = $config['bucket'];
        $file['name'] = $res[0]['key'];
        return $file;
    }else{
        return false;
    }

}


/**
 * 转换上传文件数组变量为正确的方式
 * @access private
 * @param array $files  上传的文件变量
 * @return array
 */
function dealFiles($files) {
    $fileArray  = array();
    $n          = 0;
    foreach ($files as $key=>$file){
        if(is_array($file['name'])) {
            $keys       =   array_keys($file);
            $count      =   count($file['name']);
            for ($i=0; $i<$count; $i++) {
                $fileArray[$n]['key'] = $key;
                foreach ($keys as $_key){
                    $fileArray[$n][$_key] = $file[$_key][$i];
                }
                $n++;
            }
        }else{
            $fileArray = $files;
            break;
        }
    }
    return $fileArray;
}

/**
 * 检查上传的文件
 * @param array $file 文件信息
 */
function check($file) {
    /* 文件上传失败，捕获错误代码 */
    if ($file['error']) {
        echo $file['error'];
        return false;
    }

    /* 无效上传 */
    if (empty($file['name'])){
        echo '未知上传错误！';
    }

    /* 检查是否合法上传 */
    if (!is_uploaded_file($file['tmp_name'])) {
        echo '非法上传文件！';
        return false;
    }

    /* 通过检测 */
    return true;
}

