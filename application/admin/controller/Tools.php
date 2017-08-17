<?php
/**
 * User: Lipeng
 * Date: 2017/6/16
 * Time: 17:28
 */
namespace app\admin\controller;

class Tools extends Common {
    public $region_ids = array();
    /*----------------------------------------
     *
     *  区域模块 相关信息编辑
     *
     *---------------------------------------/

    /* 区域列表,根据参数显示 */
   public function region(){
       $parent_id = input('?param.parent_id')?input('param.parent_id'):0;
       $db = db('region');
       if($parent_id>0){
           $p_parent_info = $db->field('parent_id')->where('id',$parent_id)->find();
           $top_back_id = $p_parent_info['parent_id'];
       }else{
            $top_back_id = 0;
       }
       $region = $db->where(['parent_id'=>$parent_id])->order('id asc')->select();
       $count = $db->where(['parent_id'=>$parent_id])->count();
       return $this->fetch('',['region'=>$region,'count'=>$count,'parent_id'=>$parent_id,'top_back_id'=>$top_back_id]);
   }

   /* 新增区域，包括同级的新增和子级的新增 */
   public function add_region(){
       if ($this->request->isPost()){
            $data = input('post.');
            if ($data['name'] == ''){
                $this->error('区域名称不能为空');
            }
            if (!isset($data['parent_id'])){
                $this->error('父级区域id不能为空');
            }
            $parent_id = $data['parent_id'];
            $db = db('region');
            if ($parent_id >0){
                $parent_info = $db->where('id',$parent_id)->find();
                $data['level'] = intval($parent_info['level'])+1;
            }else{
                $data['level'] = 1;
            }
            if (isset($data['back_id'])){
                $back_id = $data['back_id'];
                unset($data['back_id']);
            }else{
                $back_id = $parent_id;
            }
            $res = $db->insert($data);
            if ($res){
                $this->success('操作成功！',url('region',array('parent_id'=>$back_id)));
            }else{
                $this->error('操作失败！');
            }
       }else{
           $parent_id = input('?param.parent_id')?input('param.parent_id'):0;
           //添加数据添加成功后，需要返回的父级id（父级的父级）
           $p_parent_info = db('region')->field('parent_id')->where('id',$parent_id)->find();
            return $this->fetch('add_region',['parent_id'=>$parent_id,'back_id'=>$p_parent_info['parent_id']]);
       }
   }

   /*删除区域，删除时候，会删除当前id，以及此id对应所以子级*/
   public function del_region(){
       $id = input('?param.id')?input('param.id'):'';
       $db = db('region');
       if (!empty($id)){
           /*$this->get_region_child(array(array('id'=>$id)),$db);
           dump($this->region_ids);*/
           $res = $db->where(['id'=>$id])->delete();
           if ($res){
               $this->success('删除成功！');
           }
       }

   }

   /**
    * 递归获取当前区域id下面所以的子id
    * @param  $ids,最顶级的id
    * @param $db,全局的数据库连接
    * @return array
    */
   private function get_region_child($ids,$db){
       foreach ($ids as $k=>$v){
           $ids = $db->field('id')->where('parent_id',$v['id'])->select();
           if($ids){
               $this->get_region_child($ids,$db);
           }else{
               array_push($this->region_ids,$v['id']);
           }
       }
   }



}