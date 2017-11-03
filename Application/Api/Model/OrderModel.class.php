<?php
namespace Api\Model;
use Think\Model;

/**
 * Class BaseModel
 * @package Manager\Model
 * 商品订单类
 */
class OrderModel extends BaseModel {
    function getList($param = array()){

    }
    function findRow($param = array()){

    }
    public function selectOrder($where = array(), $field = '*', $order = '', $page = '', $limit = '', $find = ''){
        if(!empty($page)){
            $result = $this ->alias('`order`') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = `order`.master_id',
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = `order`.m_id',
                ))
                ->field($field) ->order($order) ->page($page,10) ->select();
        }elseif(!empty($find)){
            $result = $this ->alias('`order`') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = `order`.master_id',
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = `order`.m_id',
                ))
                ->field($field) ->find();
        }elseif(!empty($limit)){
            $result = $this ->alias('`order`') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = `order`.master_id',
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = `order`.m_id',
                ))
                ->field($field) ->order($order) ->limit($limit) ->select();
        }

        return $result;
    }

    public function testOrder($where, $field, $order, $page){
        $result = $this ->alias('order') ->where($where)
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = order.master_id',
                'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = order.m_id',
            ))
            ->field($field) ->order($order) ->page($page,10) ->select();
        return $result;
    }
}