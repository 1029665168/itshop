<?php
require_once __DIR__ . '/autoload.php';

// 引入鉴权类
use Qiniu\Auth;

$config = array(
    'accessKey'			=> 'DpVpbEr0BNVvjCaQ_JnqjJj7F_4XUa8ffvwDKsAA', 		//oss 访问密钥
    'secrectKey'		=> '4uW-ID_7ZMjH1XWQAmjLKhGhi92caD-E9fNqOeO8', 		//oss 安全密钥
    'bucket'            =>  'xizisifang',
    //'domain'            =>  'http://os7bf3nmw.bkt.clouddn.com/',
    'domain'            =>  'http://image.xizisifang.com/',
);

/**
 * 下载指定文件
 * @param  array   $imgUrl    保存的文件信息
 * @param  boolean $replace 同名文件是否覆盖
 * @return boolean          保存状态，true-成功，false-失败
 */
function download($imgUrl,$save_path) {
    global $config;

    // 构建鉴权对象
    $auth = new Auth($config['accessKey'], $config['secrectKey']);
    $authUrl = $auth->privateDownloadUrl($imgUrl);
    $res = down_file($authUrl,$save_path);
    return $res;
}


function down_file($file_url, $save_to)
{
   /* $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch,CURLOPT_URL,$file_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $file_content = curl_exec($ch);
    curl_close($ch);
    $downloaded_file = fopen($save_to, 'w');
    $res = fwrite($downloaded_file, $file_content);
    fclose($downloaded_file);
    return $res;*/
    $content = file_get_contents($file_url);
    $res =  file_put_contents($save_to, $content);
    return $res;
}

/*for($i=0;$i<10;$i++){
    $time = time().rand(111,999).'.png';
    download('http://image.xizisifang.com/album/201706/1498627766258.png',$time);
}*/


