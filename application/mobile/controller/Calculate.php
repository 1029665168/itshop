<?php
/**
 * User: Lipeng
 * Date: 2017/6/16
 * Time: 17:28
 */
namespace app\mobile\controller;

class Calculate extends Common {

    public function index(){
        $week_count = 40;
        $start_time = '2017-01-18';
        $prev_time = date('Y-m-d',strtotime($start_time)+60*60*24*7*40);
        $distance_time = time() - strtotime($start_time);
        $current_week = round($distance_time/(60*60*24*7),2);
        $birth_week = $week_count-$current_week;
        /* 获取最新的2个姓名信息 */
        $child_info = db('child')->where('status',1)->order('id desc')->limit(2)->select();
        $array = [
            'start_time'=>$start_time,
            'prev_time'=>$prev_time,
            'week_count'=>$week_count,
            'current_week'=>$current_week,
            'birth_week'=>$birth_week,
            'child_info'=>$child_info,
        ];
        return $this->fetch('index',$array);
    }

    /*姓名列表-表头信息*/
    public function child_index(){
        $page = input('?param.page')?input('param.page'):1;
        return $this->fetch('',['page'=>$page]);
    }
    /*姓名列表-表体信息*/
    public function child_list(){
        // 条件列表
        $map = array();
        $map['status'] = 1;
        //分页查找数据
        $page = input('?param.page')?input('param.page'):1;
        $length =  config('page_length')==null?10: config('page_length');
        $offset = ($page-1)*$length;
        $info = db('child')->where($map)->limit($offset,$length)->select();
        return $this->fetch('child_list',['info'=>$info]);
    }
    /*添加/编辑姓名*/
    public function child_edit(){
        if($this->request->isPost()){
            $data = input('post.');
            $db = db('child');
            if($data['id']>0){  // 跟新
                // 条件列表
                $map = array();
                $map['status'] = 1;
                $map['id'] = ['<>',$data['id']];
                $map['name'] = ['=',$data['name']];
                $exist_info = $db->where($map)->count();
                if($exist_info){
                    $return_data = array(
                        'status'=>0,
                        'msg'=>'姓名重复！'
                    );
                    return $return_data;
                }
                $res = $db->update($data);
            }else{  // 新增
                // 条件列表
                $map = array();
                $map['status'] = 1;
                $map['name'] = ['=',$data['name']];
                $exist_info = $db->where($map)->count();
                if($exist_info){
                    $return_data = array(
                        'status'=>0,
                        'msg'=>'姓名重复！'
                    );
                    return $return_data;
                }
                unset($data['id']);
                $res = $db->insert($data);
            }
            if($res !== false){
                $return_data = array(
                    'status'=>1,
                    'msg'=>'操作成功！'
                );
            }else{
                $return_data = array(
                    'status'=>0,
                    'msg'=>'操作失败！'
                );
            }
            return $return_data;
        }else{
            $id = intval(input('param.id'));
            $info = array();
            if ($id>0){
                $info = db('child')->where('id',$id)->find();
            }
            return $this->fetch('child_edit',['id'=>$id,'info'=>$info]);
        }
    }

    public function child_detail(){
        $id = intval(input('param.id'));
        $info = db('child')->where('id',$id)->find();
        return $this->fetch('child_detail',['info'=>$info]);

    }

    /*删除姓名*/
    public function child_delete(){
        $id = intval(input('param.id'));
        if ($id>0){
            $res = db('child')->where('id',$id)->update(['status'=>-1]);
            if($res){
                $return_data = array(
                    'status'=>1,
                    'msg'=>'删除成功！'
                );
            }else{
                $return_data = array(
                    'status'=>0,
                    'msg'=>'删除失败！'
                );
            }
            return $return_data;
        }else{
            $return_data = array(
                'status'=>0,
                'msg'=>'非法操作！'
            );
            return $return_data;
        }

    }


}