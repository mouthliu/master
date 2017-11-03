<?php
namespace Api\Model;
use Think\Model;

/**
 * Class BaseModel
 * @package Manager\Model
 * 大师类
 */
class GoodsModel extends BaseModel {
    function getList($param = array()){

    }
    function findRow($param = array()){

    }
    public function selectGoods($where = array(), $field = '*', $order = '', $limit = '', $page = ''){
        if(empty($page)){
            $result = $this ->alias('goods') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = goods.master_id',
                    'LEFT JOIN '.C('DB_PREFIX').'goods_type goods_type ON goods_type.id = goods.goods_type',
                ))
                ->field($field) ->order($order) ->limit($limit) ->select();
        }else{
            $result = $this ->alias('goods') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = goods.master_id',
                    'LEFT JOIN '.C('DB_PREFIX').'goods_type goods_type ON goods_type.id = goods.goods_type',
                ))
                ->field($field) ->order($order) ->page($page, 10) ->select();
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
}