<?php
namespace Manager\Logic;

/**
 * Class OrderCommentLogic
 * @package Manager\Logic
 * 订单评价逻辑层
 */
class OrderCommentLogic extends BaseLogic {
    /**
     * @param array $request
     * @return array
     * 获取列表
     */
    function getList($request = array()) {
        if($request['c_nickname']){
            $param['where']['member.nickname'] = array('like','%'.$request['c_nickname'].'%');
        }
        if($request['c_order_sn']){
            $param['where']['o_order.order_sn'] = array('like','%'.$request['c_order_sn'].'%');
        }
        $param['where']['order_c.status'] = array('neq',9);
        $param['order'] = 'order_c.create_time DESC';   //排序
        $param['page_size'] = C('LIST_ROWS');      //页码
        $param['parameter']         = $request;
        $result = D('OrderComment')->getList($param);
        return $result;
    }
    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $row = D('OrderComment')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
}