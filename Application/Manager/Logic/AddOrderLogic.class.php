<?php
namespace Manager\Logic;

/**
 * Class AdvertLogic
 * @package Manager\Logic
 * 微记标签逻辑层
 */
class AddOrderLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取列表
     */
    function getList($request = array()){
        if($request['r_order_id']){
            $param['where']['r_order_id'] = $request['r_order_id'];
            $_SESSION['r_order_id'] = $request['r_order_id'];
        }else{
            $param['where']['r_order_id'] = $_SESSION['r_order_id'];
        }

        if($request['nickname']){
            $param['where']['member.nickname'] = array('like','%'.$request['nickname'].'%');
        }

        if($request['order_sn']){
            $param['where']['a_order.order_sn'] = array('like','%'.$request['order_sn'].'%');
        }

        if(isset($request['pay_type'])){
            $param['where']['a_order.pay_type'] = $request['pay_type'];
        }

        if(isset($request['pay_status'])){
            $param['where']['a_order.pay_status'] = $request['pay_status'];
        }

        $param['where']['a_order.status'] = array('neq',9);
        $param['order'] = 'a_order.create_time DESC';   //排序
        $param['page_size'] = C('LIST_ROWS');      //页码
        $param['parameter']         = $request;
        $result = D('AddOrder')->getList($param);
        return $result;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()){
        if(!empty($request['id'])) {
            $param['where']['a_order.id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $row = D('AddOrder')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }

        $row['start_time'] = date('Y-m-d', $row['start_time']);
        return $row;
    }
}