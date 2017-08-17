<?php
/**
 * User: Lipeng
 * Date: 2017/6/16
 * Time: 17:28
 */
namespace app\admin\controller;

class System extends Common {

    /* 初始化操作 */
    public function _initialize(){
       parent::_initialize();
       $request  = $this->request;
       $current_action = $request->action();
       $this->assign('current_action',$current_action);
    }

    /* 商城信息 */
    public function index(){
        $config  = $this->get_config_info('web_info');
        //获取省份信息
        $province = db('region')->field('id,name')->where(['level'=>1,'parent_id'=>0])->select();
        //获取市级信息
        $city = $this->option_area($config['web_province'],null,1,$config['web_city']);
        //获取市级信息
        $area = $this->option_area($config['web_city'],null,1,$config['web_area']);
        return $this->fetch('',['config'=>$config,'province'=>$province,'city'=>$city,'area'=>$area]);
    }

    /* 基本信息 */
    public function basic(){
        $config  = $this->get_config_info('basic');
        return $this->fetch('',['config'=>$config]);
    }

    /* 购物流程 */
    public function shopping(){
        $config  = $this->get_config_info('shopping');
        $date_array = array();
        for ($i=0;$i<30;$i++){
            $date_array[] = $i+1;
        }
        return $this->fetch('',['config'=>$config,'date_array'=>$date_array]);
    }

    /* 短信设置 */
    public function sms(){
        $config  = $this->get_config_info('sms');
        return $this->fetch('',['config'=>$config]);
    }

    /* 邮件设置 */
    public function smtp(){
        $config  = $this->get_config_info('smtp');
        return $this->fetch('',['config'=>$config]);
    }

    /* 水印设置 */
    public function water(){
        $config  = $this->get_config_info('water');
        return $this->fetch('',['config'=>$config]);
    }

    /* 分销设置 */
    public function distribut(){
        $config  = $this->get_config_info('distribut');
        $date_array = array();
        for ($i=0;$i<30;$i++){
            $date_array[] = $i+1;
        }
        return $this->fetch('',['config'=>$config,'date_array'=>$date_array]);
    }

    /**
     * 网站配置信息保存，此方法统一保存所有数据
     * 数据为$_POST/$_FILES数组信息
     */
    public function save_config(){
        if ($this->request->isPost()){
            $data = input('post.');
            //数据不为空的时候，保存数据（更新/新增）
            if(!empty($data)){
                if (isset($data['config_type'])){
                    $config_type = $data['config_type'];
                    unset($data['config_type']);
                }else{
                    $config_type = 'default';
                }
                //如果存在网站logo,移动文件
                if (isset($data['web_logo'])){
                    $move_res = $this->move_files($data['web_logo'],'logo');
                    if ($move_res){
                        $data['web_logo'] = $move_res;
                    }
                }
                //处理水印图片
                if (isset($data['water_img'])){
                    $move_res = $this->move_files($data['water_img'],'logo');
                    if ($move_res){
                        $data['water_img'] = $move_res;
                    }
                }
                $db = db('config');
                $insert_data = array();
                $update_data = array();
                /* 处理需要更新/新增的数据*/
                foreach ($data as $key=>$value) {
                    $config_info = $db->where('name', $key)->column('id');
                    if (!empty($config_info)){
                        $config_id = $config_info[0];
                    }else{
                        $config_id = false;
                    }
                    if ($config_id != false) {
                        $array = array(
                            'id' => $config_id,
                            'value' => $value
                        );
                        array_push($update_data, $array);
                    } else {
                        $array = array(
                            'name' => $key,
                            'value' => $value,
                            'config_type' => $config_type
                        );
                        array_push($insert_data, $array);
                    }
                }
                $system = new \app\admin\model\System();
                if(!empty($insert_data)){
                    $system->saveAll($insert_data);
                }
                if(!empty($update_data)){
                    $system->saveAll($update_data);
                }
                $this->success('保存成功！');
            }
        }
    }

    /**
     * 根据配置信息类型查询信息
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
        $config['config_type'] = $config_type;
        return $config;
    }



}