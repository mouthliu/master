<?php
namespace Api\Model;
use Think\Model;

/**
 * Class BaseModel
 * @package Manager\Model
 * 用户服务订单模块
 */
class ServiceOrderModel extends BaseModel {

    function getList($param = array()){

    }

    function findRow($param = array()){

    }

    public function selectServiceOrder($where = array(), $field = '*', $order = '', $page = '', $limit = '', $find = ''){
        if(!empty($page)){
            $result = $this ->alias('sorder') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = sorder.m_id',
                    'LEFT JOIN '.C('DB_PREFIX').'region region ON region.id = sorder.city',
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = sorder.master_id',
                    'LEFT JOIN '.C('DB_PREFIX').'master_service masterservice ON masterservice.id = sorder.m_s_id',
                    'LEFT JOIN '.C('DB_PREFIX').'service service ON service.id = masterservice.service_id',
                ))
                ->field($field) ->order($order) ->page($page,10) ->select();
        }

        if(!empty($limit)){
            $result = $this ->alias('sorder') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = sorder.m_id',
                    'LEFT JOIN '.C('DB_PREFIX').'region region ON region.id = sorder.city',
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = sorder.master_id',
                    'LEFT JOIN '.C('DB_PREFIX').'master_service masterservice ON masterservice.id = sorder.m_s_id',
                    'LEFT JOIN '.C('DB_PREFIX').'service service ON service.id = masterservice.service_id',
                ))
                ->field($field) ->order($order) ->limit($limit) ->select();
        }

        if(!empty($find)){
            $result = $this ->alias('sorder') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = sorder.m_id',
                    'LEFT JOIN '.C('DB_PREFIX').'region region ON region.id = sorder.city',
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = sorder.master_id',
                    'LEFT JOIN '.C('DB_PREFIX').'master_service masterservice ON masterservice.id = sorder.m_s_id',
                    'LEFT JOIN '.C('DB_PREFIX').'service service ON service.id = masterservice.service_id',
                ))
                ->field($field) ->find();
        }

        return $result;
    }
}