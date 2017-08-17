<?php
namespace Plugins\uploads;
require_once __DIR__ . '/autoload.php';

// 引入鉴权类
use Qiniu\Auth;

// 引入上传类
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;
class Uploads{

    /*上传的文件配置参数*/
    private $file_config = array(
        'mime_type'=>array('image/jpeg','image/png','image/gif'),
        'ext'=>array('gif','jpg','jpeg','png'),
        'max_size'=>100*1024*1024,
        'upload_root_file'=>'upload',
        /*七牛存储配置*/
        'accessKey'			=> 'DpVpbEr0BNVvjCaQ_JnqjJj7F_4XUa8ffvwDKsAA', 		//oss 访问密钥
        'secrectKey'		=> '4uW-ID_7ZMjH1XWQAmjLKhGhi92caD-E9fNqOeO8', 		//oss 安全密钥
        'bucket'            =>  'xizisifang',
        'domain'            =>  'http://image.xizisifang.com/',
    );

    /*---------------------------------------------------------------------------*/
    //  $_FILES 数组方式上传文件
    /*---------------------------------------------------------------------------*/
    /**
     *  上传文件-单文件上传（name=file）
     * @param $file
     * @param $dir
     * @return boolean/array
     */
    public function upload($file,$dir=''){
        $file_info = $this->save($file,$dir);
        if (count($file_info)==1){
            return $file_info[0];
        }else{
            return false;
        }
    }

    /**
     *  上传文件-多文件上传（name=file[]）
     *  @param $file
     * @param $dir
     * @return boolean/array
     */
    public function uploads($file,$dir=''){
        $file_info = $this->save($file,$dir);
        if (count($file_info)>0){
            return $file_info;
        }else{
            return false;
        }
    }


    /**
     * 上传文件-处理基本信息
     * @param  $files ，类似 $_FILES['img']数组
     * @param $dir ,文件保存的目录
     * @return array
     */
    private function save($files,$dir='') {
        // 对上传文件数组信息处理
        $files   =  $this->sortFiles($files);
        $return_info = array();
        if (!empty($files)){
            /* 检测并创建子目录 */
            $root_file = $this->file_config['upload_root_file'];
            $time_data = date('Ym',time());
            if ($dir != ''){
                $save_path = './'.$root_file.'/'.$dir.'/'.$time_data.'/';
            }else{
                $save_path = './'.$root_file.'/temp/'.$time_data.'/';
            }
            if (!file_exists($save_path)){
                mkdir($save_path,0777,true);
            }

            //循环处理文件信息
            foreach ($files as $key => $file) {
                $file['save_path'] = $save_path;    //文件目录
                $file['save_name'] = time().rand(10000,99999).'.'.$file['ext'];  //文件名
                /* 保存文件 并记录保存成功的文件 */
                $save_res = $this->move_file($file);
                if ($save_res !== false) {
                    $return_info[] = $save_res;
                }
            }
        }
        return empty($return_info) ? false : $return_info;

    }

    /**
     * 上传文件-保存文件
     * @param  array   $file    保存的文件信息
     * @return boolean/array    保存状态，array-成功，false-失败
     */
    private function move_file($file) {
        $res = move_uploaded_file($file['tmp_name'],$file['save_path'].$file['save_name']);
        if ($res){
            $save_res = array(
                'save_path'=>  $file['save_path'],
                'save_name'=>   $file['save_name'],
                'full_path'=>substr($file['save_path'].$file['save_name'],1)
            );
            return $save_res;
        }else{
            return false;
        }

    }



    /**
     * 转换上传文件数组转换为正确的方式，并且检查文件是否完整
     * @access private
     * @param array $files  上传的文件变量
     * @return array
     */
    private function sortFiles($files) {
        //整理上传的文件
        $fileArray  = array();
        if (isset($files['name']) && is_array($files['name'])){
            $keys = array_keys($files);
            $array_num = count($files['name']);
            $every_array = array();
            for ($i=0;$i<$array_num;$i++){
                foreach ($keys as $kv){
                    $every_array[$kv] = $files[$kv][$i];
                }
                $fileArray[] = $every_array;
            }
        }elseif (isset($files['name']) && is_string($files['name']) && $files['name']!=''){
            $fileArray[] = $files;
        }
        //检测上传的文件
        $fileList = array();
        if (!empty($fileArray)){
            foreach ($fileArray as $key=>$value) {
                $check_res = $this->check($value);
                if ($check_res !== false){  //只返回符合配置的文件
                    $fileList[] = $check_res;
                }
            }
        }
        unset($fileArray);
        return $fileList;
    }

    /**
     * 检查上传的文件
     * @param array $file 文件信息
     * @return array
     */
    private function check($file) {
        /* 文件上传失败，捕获错误代码 */
        if ($file['error']>0) {
            return false;
        }

        /* 无效上传 */
        if (empty($file['name'])){
            return false;
        }

        /* 检查是否合法上传 */
        if (!is_uploaded_file($file['tmp_name'])) {
            return false;
        }

        /*文件上传类型检测*/
        if (function_exists('finfo_open')){
            $file_info    = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($file_info, $file['tmp_name']);
            finfo_close($file_info);
        }elseif (function_exists('mime_content_type')){
            $mime_type = mime_content_type($file['tmp_name']);
        }else{
            $mime_type = $file['type'];
        }
        $allow_type = $this->file_config['mime_type'];
        if (!empty($allow_type)){
            if (!in_array($mime_type,$allow_type)){
                return false;
            }
        }

        /*文件后缀检测*/
        $file_ext    =   pathinfo(strip_tags($file['name']), PATHINFO_EXTENSION);
        $allow_ext = $this->file_config['ext'];
        if (!empty($allow_ext)){
            if (!in_array($file_ext,$allow_ext)){
                return false;
            }
        }
        $file['ext'] = $file_ext;

        /*文件大小检测*/
        isset($this->file_config['max_size'])?$allow_size = intval($this->file_config['max_size']):$allow_size=0;
        if ($allow_size>0 && $allow_size<intval($file['size'])){
            return false;
        }
        /* 通过检测 */
        return $file;
    }

    /*---------------------------------------------------------------------------*/
    // 七牛文件上传
    /*---------------------------------------------------------------------------*/
    /**
     *  七牛上传文件，文件域方式-多文件上传（name=file[]）
     *  @param $file
     * @param  $user_id ,用户在当前网站上的id，如果要区分管理员还是普通用户，在id前面加上标识
     * @param $dir
     * @return boolean/array
     */
    public function qiniu_uploads($file,$user_id=0,$dir=''){
        $file_info = $this->qiniu_save($file,$user_id,$dir);
        if (count($file_info)>0){
            return $file_info;
        }else{
            return false;
        }
    }


    /**
     * 七牛上传文件-处理基本信息
     * @param  $files ，类似 $_FILES['img']数组
     * @param $dir ,文件保存的目录
     * @param $user_id ,用户在当前网站上的id
     * @return array
     */
    private function qiniu_save($files,$user_id,$dir='') {
        // 对上传文件数组信息处理
        $files   =  $this->sortFiles($files);
        $return_info = array();
        if (!empty($files)){
            /* 检测并创建子目录 */
            $root_file = $this->file_config['upload_root_file'];
            $time_data = date('Ym',time());
            if ($dir != ''){
                $save_path = $root_file.'/'.$user_id.'/'.$dir.'/'.$time_data.'/';
            }else{
                $save_path = $root_file.'/'.$user_id.'/temp/'.$time_data.'/';
            }
            // 构建鉴权对象
            $auth = new Auth($this->file_config['accessKey'], $this->file_config['secrectKey']);

            // 生成上传 Token
            $token = $auth->uploadToken($this->file_config['bucket']);

            // 初始化 UploadManager 对象并进行文件的上传。
            $uploadMgr = new UploadManager();
            //循环处理文件信息
            foreach ($files as $key => $file) {
                $file['save_path'] = $save_path;    //文件目录
                $file['save_name'] = $file['name'];  //文件名
                // 调用 UploadManager 的 putFile 方法进行文件的上传。
                $save_res = $uploadMgr->putFile($token, $file['save_path'].$file['save_name'], $file['tmp_name']); //保存文件到远程服务器
                if ($save_res !== false) {
                    $res_file['url'] =  $this->file_config['domain'].$save_res[0]['key'];
                    $res_file['domain'] = $this->file_config['domain'];
                    $res_file['bucket'] = $this->file_config['bucket'];
                    $res_file['name'] = $save_res[0]['key'];
                    $return_info[] = $res_file;
                }
            }
        }
        return empty($return_info) ? false : $return_info;

    }

    public function qiniu_uploads_local($file,$user_id=0,$dir=''){
        /* 检测并创建子目录 */
        $root_file = $this->file_config['upload_root_file'];
        $time_data = date('Ym',time());
        if ($dir != ''){
            $save_path = $root_file.'/'.$user_id.'/'.$dir.'/'.$time_data.'/';
        }else{
            $save_path = $root_file.'/'.$user_id.'/temp/'.$time_data.'/';
        }
        // 构建鉴权对象
        $auth = new Auth($this->file_config['accessKey'], $this->file_config['secrectKey']);

        // 生成上传 Token
        $token = $auth->uploadToken($this->file_config['bucket']);

        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();
        $save_name  = '11.jpg';
        $save_res = $uploadMgr->putFile($token, $save_path.$save_name, $file); //保存文件到远程服务器
        var_dump($save_res);
    }

    /**
     * 七牛文件下载
     * @param  array   $imgUrl    保存的文件信息
     * @return boolean          保存状态，true-成功，false-失败
     */
    function qiniu_download($imgUrl,$save_path) {
        global $config;

        // 构建鉴权对象
        $auth = new Auth($config['accessKey'], $config['secrectKey']);
        $authUrl = $auth->privateDownloadUrl($imgUrl);
        $res = $this->niqiu_down_file($authUrl,$save_path);
        return $res;
    }


   private function niqiu_down_file($file_url, $save_to)
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

    /**
     * 七牛文件删除
     * @param  string   $file   要删除的文件路径
     * @return boolean          保存状态，true-成功，false-失败
     */
    function qiniu_delete($file) {
        // 构建鉴权对象
        $auth = new Auth($this->file_config['accessKey'], $this->file_config['secrectKey']);
        $bucketMgr = new BucketManager($auth);
        $err = $bucketMgr->delete($this->file_config['bucket'], $file);
        if ($err == null){
            return true;
        }else{
            return false;
        }
    }
}


