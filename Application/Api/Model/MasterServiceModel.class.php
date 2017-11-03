<?php
namespace Api\Model;
use Think\Model;

/**
 * Class BaseModel
 * @package Manager\Model
 * 大师类
 */
class MasterServiceModel extends BaseModel {
    function getList($param = array()){

    }
    function findRow($param = array()){

    }
    public function typeMaster($where = array(), $field = '*',$order = ''){
        $result = M('MasterService') ->alias('m_s') ->where($where)
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = m_s.master_id',
            ))
            ->field($field) ->order($order) ->select();
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

    public function selectServiceOrder($where = '', $field = '', $order = '', $page = '', $limit = '', $find = ''){
        if($page){
            $result = M('ServiceOrder') ->alias('serviceorder') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = serviceorder.master_id',
                    'LEFT JOIN '.C('DB_PREFIX').'master_service masterservice ON masterservice.id = serviceorder.m_s_id',
                    'LEFT JOIN '.C('DB_PREFIX').'service service ON service.id = masterservice.service_id',
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = serviceorder.m_id',
                ))
                ->field($field) ->order($order) ->page($page, 10) ->select();
        }

        if($limit){
            $result = M('ServiceOrder') ->alias('serviceorder') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = serviceorder.master_id',
                    'LEFT JOIN '.C('DB_PREFIX').'master_service masterservice ON masterservice.id = serviceorder.m_s_id',
                    'LEFT JOIN '.C('DB_PREFIX').'service service ON service.id = masterservice.service_id',
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = serviceorder.m_id',
                ))
                ->field($field) ->order($order) ->limit($limit) ->select();
        }

        if($find){
            $result = M('ServiceOrder') ->alias('serviceorder') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = serviceorder.master_id',
                    'LEFT JOIN '.C('DB_PREFIX').'master_service masterservice ON masterservice.id = serviceorder.m_s_id',
                    'LEFT JOIN '.C('DB_PREFIX').'service service ON service.id = masterservice.service_id',
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = serviceorder.m_id',
                ))
                ->field($field) ->find();
        }

        return $result;
    }
}