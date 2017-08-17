<?php
namespace Plugins;

class Upload{

    /*上传的文件配置参数*/
    private $file_config = array(
        'mime_type'=>array('image/jpeg','image/png','image/gif'),
        'ext'=>array('gif','jpg','jpeg','png'),
        'max_size'=>100*1024*1024,
        'upload_root_file'=>'upload'
    );

    /*---------------------------------------------------------------------------*/
    //  $_FILES 数组方式上传文件
    /*---------------------------------------------------------------------------*/
    /**
     *  上传文件-单文件上传（type=file）
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
     *  上传文件-多文件上传（type=file[]）
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

    /**
     * 检查数组的维数，一、二维数组
     * @param array $file 文件信息
     * @return int  $num
     */
    private function get_dimension($file){
        $num=0;
       if (is_array($file)){
            $num++;
            foreach ($file as $v){
                if (is_array($v)){
                    $num++;
                    break;
                }
            }
       }
       return $num;
    }

    /**
     * 检查数组的维数，支持多维
     * @param array $file 文件信息
     * @return int  $num
     */
    private function get_dimensions($file){
            $al = array(0);
            function aL($arr,&$al,$level=0){
                if(is_array($arr)){
                    $level++;
                    $al[] = $level;
                    foreach($arr as $v){
                        aL($v,$al,$level);
                    }
                }
            }
            aL($file,$al);
            return max($al);
    }


    /*---------------------------------------------------------------------------*/
    //  文件流方式上传文件，base64
    /*---------------------------------------------------------------------------*/
    /**
     * 上传文件-base64
     * @param  $base64_contents ，base64源数据
     * @param $dir ,文件保存的目录
     * @return boolean/array
     */
    public function upload_base64($base64_contents,$dir='')
    {
        if (!empty($base64_contents)){
            $base_info = explode(',',$base64_contents);
            $base64_content = $base_info[1];    //数据完整的内容

            /*文件类型处理*/
            $file_type=explode(';',$base_info[0]);
            $file_type = substr($file_type[0],5);

            /*文件类型检测*/
            $allow_type = $this->file_config['mime_type'];
            if (!empty($allow_type)){
                if (!in_array($file_type,$allow_type)){
                    return false;
                }
            }
            $ext = $this->mime_type_to_ext($file_type);
            if ($ext != null){
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
                $save_name = time().rand(10000,99999).'.'.$ext;  //文件名
                $full_path = $save_path.$save_name;
                $file_info = base64_decode($base64_content);
                $res = file_put_contents($full_path,$file_info);
                if ($res){
                    $return_array = array(
                        'save_path' => $save_path,
                        'save_name' => $save_name,
                        'full_path' => substr($full_path,1)
                    );
                    return $return_array;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }
    }

    /*简单的类型转换*/
    private function mime_type_to_ext($mime_type){
        $array = array(
            'image/jpeg'=>'jpg',
            'image/png'=>'png',
            'image/gif'=>'gif'
        );
        if (isset($array[$mime_type])){
            return $array[$mime_type];
        }else{
            return null;
        }
    }

    /*---------------------------------------------------------------------------*/
    //  文件流方式上传文件，二进制
    /*---------------------------------------------------------------------------*/
    /**
     * webuploader 文件上传 ,需要百度插件（webuploader）
     * 此方法需要前台把文件分成二进制包，分块处理，调用百度插件（webuploader）
     * @param $dir ,文件保存的目录
     * @return string
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
                 'filePaht'=>$uploadPath,
                 'fileSuffixes'=>$pathInfo['extension'],
             ];

             die(json_encode($response));
        }

        // Return Success JSON-RPC response
        die( '{"jsonrpc" : "2.0", "result" : "success", "id" : "'.$chunk.'"}');
    }
}


