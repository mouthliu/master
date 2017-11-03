<?php
namespace Wap\Controller;
use Think\Controller;
class BaseController extends Controller{
    /**
     * 初始化
     */
    public function _initialize(){

    }

    /**
     * 单条查询或者多条查询
     */
    public function easyMysql($model, $type, $where = '', $data = '', $field = '*', $order = '', $page = '', $limit = ''){
        if($type == 1 && $data != ''){
            //type == 1  新增
            $result = M($model) ->add($data);
        }

        if($type == 2 && $data != ''){
            //type == 2  修改
            $result = M($model) ->where($where) ->data($data) ->save();
        }

        if($type == 3){
            //type == 3  find查询
            $result = M($model) ->where($where) ->field($field) ->order($order) ->find();
        }

        if($type == 4){
            //type == 4  多条查询
            if($page == ''){
                $result = M($model) ->where($where) ->field($field) ->order($order) ->limit($limit) ->select();
            }else{
                $result = M($model) ->where($where) ->field($field) ->order($order) ->page($page, 10) ->select();
            }
        }

        if($type == 5){
            $result = M($model) ->where($where) ->getField($field);
        }

        if($type == 6){
            $result = M($model) ->where($where) ->count();
        }

        return $result;
    }

    function searchPhoto($id){
        $path = $this ->easyMysql('File','5',array('id'=>$id),'','path');
        $picture = $path?C('API_URL').$path:'';
        return $picture;
    }

    public function serviceOrderNum($master_id){
        $where = array('master_id'=>$master_id);
        $order_num = M('ServiceOrder') ->where($where) ->count();
        if(!$order_num){
            $order_num = '0';
        }
        return $order_num;
    }

    public function showService($where = array(), $field = '*',$order = '', $page = ''){
        if($page = ''){
            $result = M('MasterService') ->alias('m_s') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'service service ON service.id = m_s.service_id',
                ))
                ->field($field) ->order($order) ->select();
        }else{
            $result = M('MasterService') ->alias('m_s') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'service service ON service.id = m_s.service_id',
                ))
                ->field($field) ->order($order) ->page($page, 10) ->select();
        }

        return $result;
    }

    public function commentList($where = array(), $field = '*',$order = '', $page = '', $limit){
        if(empty($page)){
            $result = M('Comment') ->alias('comment') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = comment.m_id',
                ))
                ->field($field) ->order($order) ->limit($limit) ->select();
        }else{
            $result = M('Comment') ->alias('comment') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = comment.m_id',
                ))
                ->field($field) ->order($order) ->page($page, 10) ->select();
        }

        return $result;
    }

    public function getOrderNum($goods_id){
        $where['_string'] = " ( goods_id = ".$goods_id.") OR ( goods_id like '%,".$goods_id.",%') OR ( goods_id like '%,".$goods_id."') OR ( goods_id like '".$goods_id.",%' )";
        $where['order_status'] = array('in','1,2,3,4,6,7');
        $order_num = M('Order') ->where($where) ->count();
        if(!$order_num){
            $order_num = '0';
        }
        return $order_num;
    }

    public function selectNews($where = array(), $field = '*', $order = '', $limit = '', $page = ''){
        if(!empty($page)){
            $result = M('News') ->alias('news') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = news.master_id',
                    'LEFT JOIN '.C('DB_PREFIX').'news_type news_type ON news_type.id = news.news_type',
                ))
                ->field($field) ->order($order) ->page($page,10) ->select();
        }elseif($limit == 1){
            $result = M('News') ->alias('news') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = news.master_id',
                    'LEFT JOIN '.C('DB_PREFIX').'news_type news_type ON news_type.id = news.news_type',
                ))
                ->field($field) ->find();
        }else{
            $result = M('News') ->alias('news') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = news.master_id',
                    'LEFT JOIN '.C('DB_PREFIX').'news_type news_type ON news_type.id = news.news_type',
                ))
                ->field($field) ->order($order) ->limit($limit) ->select();
        }

        return $result;
    }

    public function selectAnswerList($where = '', $field = '', $order = '', $page = '', $limit = '', $find = ''){
        if($page){
            $result = M('RorderAnswer') ->alias('answer') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = answer.master_id',
                    'LEFT JOIN '.C('DB_PREFIX').'reward_order rorder ON rorder.id = answer.r_o_id',
                    'LEFT JOIN '.C('DB_PREFIX').'reward reward ON reward.id = rorder.reward_id',
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = rorder.m_id',
                ))
                ->field($field) ->order($order) ->page($page, 10) ->select();
        }

        if($limit){
            $result = M('RorderAnswer') ->alias('answer') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = answer.master_id',
                    'LEFT JOIN '.C('DB_PREFIX').'reward_order rorder ON rorder.id = answer.r_o_id',
                    'LEFT JOIN '.C('DB_PREFIX').'reward reward ON reward.id = rorder.reward_id',
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = rorder.m_id',
                ))
                ->field($field) ->order($order) ->limit($limit) ->select();
        }

        if($find){
            $result = M('RorderAnswer') ->alias('answer') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = answer.master_id',
                    'LEFT JOIN '.C('DB_PREFIX').'reward_order rorder ON rorder.id = answer.r_o_id',
                    'LEFT JOIN '.C('DB_PREFIX').'reward reward ON reward.id = rorder.reward_id',
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = rorder.m_id',
                ))
                ->field($field) ->find();
        }

        return $result;
    }

    public function findGoods($where = array(), $field = '*'){
        $result = M('Goods') ->alias('goods') ->where($where)
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = goods.master_id',
                'LEFT JOIN '.C('DB_PREFIX').'goods_type goods_type ON goods_type.id = goods.goods_type',
            ))
            ->field($field) ->find();

        return $result;
    }

    public function selectComment($where = array(), $field = '*', $order = '', $limit = '', $page = '', $find = ''){
        if(!empty($limit)){
            $result = M('OrderComment') ->alias('comment') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = comment.m_id',
                ))
                ->field($field) ->order($order) ->limit($limit) ->select();
        }

        if(!empty($page)){
            $result = M('OrderComment') ->alias('comment') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = comment.m_id',
                ))
                ->field($field) ->order($order) ->page($page, 10) ->select();
        }

        if(!empty($find)){
            $result = M('OrderComment') ->alias('comment') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = comment.m_id',
                ))
                ->field($field) ->find();
        }

        return $result;
    }
}