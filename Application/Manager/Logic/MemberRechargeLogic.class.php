<?php
namespace Manager\Logic;

/**
 * Class RechargeLogic
 * @package Manager\Logic
 * 充值数据层
 */
class MemberRechargeLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取列表
     */
    function getList($request = array()) {
        if(!empty($request['nickname'])) {
            $param['where']['nickname'] = array('like','%'.$request['nickname'].'%');
        }
        if(!empty($request['order_sn'])){
            $param['where']['order_sn'] = array('like','%'.$request['order_sn'].'%');
        }
        if($request['pay_type']){
            $param['where']['pay_type'] = $request['pay_type'];
        }
        if(!empty($request['start_time']) && !empty($request['end_time'])){
            $start_time = strtotime($request['start_time']);
            $end_time   = strtotime($request['end_time']);
            $param['where']['recharge.create_time']   = array('between',"$start_time,$end_time");
        }elseif(!empty($request['start_time']) && empty($request['end_time'])){
            $start_time = strtotime($request['start_time']);
            $param['where']['recharge.create_time']   = array('egt',"$start_time");
        }elseif(empty($request['start_time']) && !empty($request['end_time'])){
            $end_time   = strtotime($request['end_time']);
            $param['where']['recharge.create_time']   = array('elt',"$end_time");
        }else{

        }

        $param['where']['recharge.status']   = array('lt',9);        //状态
        $param['order'] = 'create_time DESC';   //排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('MemberRecharge')->getList($param);

        return $result;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()) {
    }

}