<?php
namespace Api\Model;
use Think\Model;

/**
 * Class BaseModel
 * @package Manager\Model
 * 收藏模块类
 */
class CollectModel extends BaseModel {
    function getList($param = array()){

    }
    function findRow($param = array()){

    }
    public function searchCollect($where = array(), $field = '*', $order = '', $page = '', $limit = '', $find = ''){

        if(!empty($page)){
            $result = M('Collect') ->alias('collect') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'goods goods   ON goods.id = collect.goods_id',
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = goods.master_id',
                ))
                ->field($field) ->order($order) ->page($page, 10) ->select();
        }

        if(!empty($limit)){
            $result = M('Collect') ->alias('collect') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'goods goods   ON goods.id = collect.goods_id',
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = goods.master_id',
                ))
                ->field($field) ->order($order) ->limit($limit) ->select();
        }

        if(!empty($find)){
            $result = M('Collect') ->alias('collect') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'goods goods   ON goods.id = collect.goods_id',
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = goods.master_id',
                ))
                ->field($field) ->find();
        }

        return $result;
    }

    public function searchFollow($where = array(), $field = '*', $order = '', $page = '', $limit = '', $find = ''){

        if(!empty($page)){
            $result = M('Follow') ->alias('follow') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = follow.master_id',
                ))
                ->field($field) ->order($order) ->page($page, 10) ->select();
        }

        if(!empty($limit)){
            $result = M('Follow') ->alias('follow') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = follow.master_id',
                ))
                ->field($field) ->order($order) ->limit($limit) ->select();
        }

        if(!empty($find)){
            $result = M('Follow') ->alias('follow') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = follow.master_id',
                ))
                ->field($field) ->find();
        }

        return $result;
    }
}