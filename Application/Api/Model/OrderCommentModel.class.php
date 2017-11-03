<?php
namespace Api\Model;
use Think\Model;

/**
 * Class BaseModel
 * @package Manager\Model
 * 商品订单评价类
 */
class OrderCommentModel extends BaseModel {
    function getList($param = array()){

    }
    function findRow($param = array()){

    }
    public function selectComment($where = array(), $field = '*', $order = '', $page = '', $limit = '', $find = ''){
        if(!empty($page)){
            $result = $this ->alias('comment') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = comment.m_id',
                    'LEFT JOIN '.C('DB_PREFIX').'goods goods ON goods.id = comment.goods_id',
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = goods.master_id',
                ))
                ->field($field) ->order($order) ->page($page,10) ->select();
        }elseif(!empty($find)){
            $result = $this ->alias('comment') ->where($where)
                ->join(array(

                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = comment.m_id',
                    'LEFT JOIN '.C('DB_PREFIX').'goods goods ON goods.id = comment.goods_id',
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = goods.master_id',
                ))
                ->field($field) ->find();
        }elseif(!empty($limit)){
            $result = $this ->alias('comment') ->where($where)
                ->join(array(

                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = comment.m_id',
                    'LEFT JOIN '.C('DB_PREFIX').'goods goods ON goods.id = comment.goods_id',
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = goods.master_id',
                ))
                ->field($field) ->order($order) ->limit($limit) ->select();
        }

        return $result;
    }
}