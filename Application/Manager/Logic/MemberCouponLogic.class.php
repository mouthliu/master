<?php
namespace Manager\Logic;

/**
 * Class MemberCouponLogic
 * @package Manager\Logic
 * 用户优惠券数据层
 */
class MemberCouponLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取列表
     */
    function getList($request = array()) {
        if(!empty($request['coupon_id'])){
            $param['where']['coupon_id'] = $request['coupon_id'];
            $_SESSION['coupon_id'] = $request['coupon_id'];
        }else{
            $param['where']['coupon_id'] = $_SESSION['coupon_id'];
        }
        if(!empty($request['nickname'])){
            $param['where']['nickname'] = array('like','%'.$request['nickname'].'%');
        }
    	$param['where']['member_coupon.status']   = array('lt',9);        //状态
        $param['order']              = 'create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数
        $result = D('MemberCoupon')->getList($param);
        foreach($result['list'] as $k => $v){
            $result['list'][$k]['end_time'] = $v['end_time'] - 1;
        }
        return $result;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()) {
    }

    /**
     * @param array $request
     * @return array
     * 获取列表
     */
    function memberGetList($request = array()) {
        if(!empty($request['m_id'])){
            $param['where']['m_id'] = $request['m_id'];
            $_SESSION['m_id'] = $request['m_id'];
        }else{
            $param['where']['m_id'] = $_SESSION['m_id'];
        }
        if(!empty($request['title'])){
            $param['where']['m_coupon.title'] = array('like','%'.$request['title'].'%');
        }
        if(!empty($request['status'])){
            $param['where']['m_coupon.status'] = $request['status'];
        }else{
            $param['where']['m_coupon.status']   = array('lt',9);        //状态
        }

        $param['where']['m_coupon.end_time'] = array('gt',time());
        $param['order']              = 'm_coupon.create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数
        $result = D('MemberCoupon')->memberGetList($param);
        foreach($result['list'] as $k => $v){
            $result['list'][$k]['end_time'] = $v['end_time'] - 1;
        }
        return $result;
    }
}