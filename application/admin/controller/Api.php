<?php
/**
 * 主要负责ajax请求数据的返回，返回的数据类型为json格式
 * User: Lipeng
 * Date: 2017/6/16
 * Time: 17:28
 */
namespace app\admin\controller;


class Api extends Common {

    /**
     *  后台获取区域信息-省级信息
     * @return  array
     */
    public function get_province(){
        //获取市级信息
        $province = $this->option_area(0,1,0,null);
        return $province;
    }

    /**
     *  后台获取区域信息-市级信息
     * @return  array
     */
    public function get_city(){
        $level = input('?param.level')?input('param.level'):null;
        $parent_id = input('?param.parent_id')?input('param.parent_id'):null;
        //获取市级信息
        $city = $this->option_area($parent_id,$level,0,null);
        return $city;
    }

    /**
     *  后台获取区域信息-区级信息
     * @return  array
     */
    public function get_area(){
        config('default_return_type','json');
        $level = input('?param.level')?input('param.level'):null;
        $parent_id = input('?param.parent_id')?input('param.parent_id'):null;
        $area = $this->option_area($parent_id,$level,0,null);
        return $area;
    }

}