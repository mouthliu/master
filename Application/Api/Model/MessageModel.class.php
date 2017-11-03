<?php
namespace Api\Model;
use Think\Model;

/**
 * Class BaseModel
 * @package Manager\Model
 * 信息表
 */
class MessageModel extends BaseModel {
    function getList($param = array()){

    }
    function findRow($param = array()){

    }
    public function selectRewardOrder($where = '', $field = '', $order = '', $page = '', $type = '', $limit = ''){
        if($type == 1){
            $result = M('RewardOrder') ->alias('rorder') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'reward reward ON reward.id = rorder.reward_id',
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = rorder.m_id',
                ))
                ->field($field) ->order($order) ->page($page, 10) ->select();
        }elseif($type == 2){
            $result = M('RewardOrder') ->alias('rorder') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'reward reward ON reward.id = rorder.reward_id',
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = rorder.m_id',
                ))
                ->field($field) ->find();
        }elseif($type == 3){
            $result = M('RewardOrder') ->alias('rorder') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'reward reward ON reward.id = rorder.reward_id',
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = rorder.m_id',
                ))
                ->field($field) ->order($order) ->limit($limit) ->select();
        }

        return $result;
    }

    public function selectAnswer($where = '', $field = '', $order = '', $type = ''){
        if($type == 1){
            $result = M('RorderAnswer') ->alias('answer') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = answer.master_id',
                ))
                ->field($field) ->order($order) ->select();
        }elseif($type == 2){
            $result = M('RorderAnswer') ->alias('answer') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = answer.master_id',
                ))
                ->field($field) ->find();
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
}