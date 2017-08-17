<?php
/**
 * User: Lipeng
 * Date: 2017/6/16
 * Time: 17:28
 */
namespace app\admin\controller;

use think\Controller;

class Common extends Controller {

    /* 初始化操作，判断登录，已经权限等 */
    public function _initialize(){
        $request  = $this->request;
        $is_login = $this->is_login();

        /* 未登录可以访问的控制和方法 */
        $allow_ac = array('login/index','login/vertify','login/logout');
        $current_ac = strtolower($request->controller().'/'.$request->action());
        if ($is_login==false && !in_array($current_ac,$allow_ac)){
            $this->redirect(url('login/index'));
        }

    }

    /* 判断后台管理员登陆状态 */
    private function is_login(){
        $user_id = session('?manager_id')?session('manager_id'):false;
        if ($user_id){
            return true;
        }else{
            return false;
        }
    }

    /*后台密码统一加密方式*/
    protected  function md5_password($password=''){
        $password = trim($password);
        $password = md5(md5(config('md5_unique_char')).md5($password));
        return $password;
    }

    /**
     *  移动文件
     *  @param $original_file,原文件名称，包含完整路径信息
     *  @param  $new_dir ,移动后的文件夹名称
     * @return boolean or string
     */
    protected function move_files($original_file,$new_dir){
        if (!empty($original_file)){
            $original_file = '.'.$original_file;
            if(file_exists($original_file)){
                $old_file_dir =  dirname($original_file);
                $new_file_name = basename($original_file);
                if(stripos($old_file_dir,'file_tmp') > 0){
                    $new_file_dir = str_replace('file_tmp',$new_dir,$old_file_dir);
                    if (!file_exists($new_file_dir)){
                        @mkdir($new_file_dir,0777,true);
                    }
                    $dest_path = $new_file_dir.'/'.$new_file_name;
                    $res = @copy($original_file,$dest_path);
                    if ($res){
                        @unlink($original_file);
                        return substr($dest_path,1);
                    }else{
                        return false;
                    }
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * 区域信息的组装,option选项中的内容
     * @param $level，区域等级
     * @param  $parent_id, 父级id
     * @param  $is_select, 是否带选择的状态，编辑，查看的时候此值为1 ，其他为0
     * @param  $select_id, 如果带默认选中，请填写此值
     * @return string
     */
    public function option_area($parent_id,$level=null,$is_select=0,$select_id=null){
        $option='';
        if($parent_id>=0){
            $map['parent_id'] = $parent_id;
            if($level){
                $map['level'] = $level;
            }
            $option_info = db('region')->field('id,name')->where($map)->select();
            if($option_info){
                if($is_select ==1 && $select_id>0){
                    foreach ($option_info as $k=>$v){
                        if($v['id']==$select_id){
                            $option.='<option value="'.$v['id'].'" selected="selected">'.$v['name'].'</option>';
                        }else{
                            $option.='<option value="'.$v['id'].'">'.$v['name'].'</option>';
                        }
                    }
                }else{
                    foreach ($option_info as $k=>$v){
                        $option.='<option value="'.$v['id'].'">'.$v['name'].'</option>';
                    }
                }
            }
        }
        return $option;
    }


    /*---------------------------------------------------------------------------*/
    //  文件上传方式集合
    /*---------------------------------------------------------------------------*/

    /**
     * webuploader 文件上传 ,需要百度插件（webuploader）
     * 此方法需要前台把文件分成二进制包，分块处理，调用百度插件（webuploader）
     * @param $dir ,文件保存的目录
     * @return string,上传后的文件路径
     */
    public function web_uploader($dir='')
    {
        $dir_separator = "/";
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit; // finish preflight CORS requests here
        }
        if ( !empty($_REQUEST[ 'debug' ]) ) {
            $random = rand(0, intval($_REQUEST[ 'debug' ]) );
            if ( $random === 0 ) {
                header("HTTP/1.0 500 Internal Server Error");
                exit;
            }
        }
        @set_time_limit(5 * 60);
        $targetDir = 'upload'.$dir_separator.'file_tmp';
        if ($dir == ''){
            $uploadDir = 'upload'.$dir_separator.'file_upload'.$dir_separator.date('Ym',time());
        }else{
            $uploadDir = 'upload'.$dir_separator.$dir.$dir_separator.date('Ym',time());
        }

        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds
        // Create target dir
        if (!file_exists($targetDir)) {
            @mkdir($targetDir,0777,true);
        }
        // Create target dir
        if (!file_exists($uploadDir)) {
            @mkdir($uploadDir,0777,true);
        }
        // Get a file name
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }
        $oldName = $fileName;
        $filePath = $targetDir . $dir_separator . $fileName;   //完整的临时文件路径

        // Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;    //当前分块索引
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1; //总分块数量

        // Remove old temp files
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                die( '{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "'.$chunk.'"}');
            }
            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . $dir_separator . $file;
                // If temp file is current file proceed to the next
                if ($tmpfilePath == "{$filePath}_{$chunk}.part" || $tmpfilePath == "{$filePath}_{$chunk}.parttmp") {
                    continue;
                }
                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.(part|parttmp)$/', $file) && (@filemtime($tmpfilePath) < time() - $maxFileAge)) {
                    @unlink($tmpfilePath);
                }
            }
            closedir($dir);
        }
        // Open temp file
        if (!$out = @fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
            die( '{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "'.$chunk.'"}');
        }
        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                die( '{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "'.$chunk.'"}');
            }
            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                die( '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "'.$chunk.'"}');
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                die( '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "'.$chunk.'"}');
            }
        };
        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }
        @fclose($out);
        @fclose($in);
        rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");

        /*判断所有文件是否已经上传完毕*/
        $done = true;
        for($index = 0; $index < $chunks; $index++ ) {
            if ( !file_exists("{$filePath}_{$index}.part") ) {
                $done = false;
                break;
            }
        }

        /*所有文件上传完毕，执行组合*/
        if ( $done ) {
            /*重新命名，避免重复*/
            $pathInfo = pathinfo($fileName);
            $hashStr = substr(md5($pathInfo['basename']),8,16);
            $hashName = time() . $hashStr . '.' .$pathInfo['extension'];
            $uploadPath = $uploadDir . $dir_separator .$hashName;

            if (!$out = @fopen($uploadPath, "wb")) {
                die( '{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "'.$chunk.'"}');
            }
            if ( flock($out, LOCK_EX) ) {
                for( $index = 0; $index < $chunks; $index++ ) {
                    if (!$in = @fopen("{$filePath}_{$index}.part", "rb")) {
                        break;
                    }
                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }
                    @fclose($in);
                    @unlink("{$filePath}_{$index}.part");
                }
                flock($out, LOCK_UN);
            }
            @fclose($out);
            $response = [
                'jsonrpc'=>'1',
                'result'=>'success',
                'oldName'=>$oldName,
                'filePaht'=>'/'.$uploadPath,
                'fileSuffixes'=>$pathInfo['extension'],
            ];

            die(json_encode($response));
        }

        // Return Success JSON-RPC response
        die( '{"jsonrpc" : "2.0", "result" : "success", "id" : "'.$chunk.'"}');
    }

}