<?php
namespace Api\Model;
use Think\Model;

/**
 * Class BaseModel
 * @package Manager\Model
 * 用户商品类
 */
class MemberGoodsModel extends BaseModel {
    function getList($param = array()){

    }
    function findRow($param = array()){

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