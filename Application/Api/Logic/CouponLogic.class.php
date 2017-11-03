<?php
namespace Api\Logic;
/**
 * Class CouponLogic
 * @package Api\Logic
 * 优惠券模块
 */
class CouponLogic extends BaseLogic{

    /**
     * 优惠券列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id：m_id
     * 类型：type=1 可以使用的优惠券，2已过期  3已使用的优惠券
     */
    public function couponList($request = array()){
        $member = $this ->searchMember($request['token']);
        if($request['type'] == 1){
            $where = array('m_id'=>$member['id'], 'status'=>0, 'start_time'=>array('lt',time()), 'end_time'=>array('gt',time()));
        }elseif($request['type'] == 2){
            $where = array('m_id'=>$member['id'], 'status'=>1);
        }else{
            $where = array('m_id'=>$member['id'], 'status'=>0, 'end_time'=>array('lt',time()));
        }
        $field = 'id as coupon_id, satisty_price, discount_price, start_time, end_time, status';
        $order = 'create_time desc';
        $coupon = $this ->easyMysql('MemberCoupon',4,$where,'',$field,$order,$request['p']);
        if(!$coupon){
            $coupon = array();
        }else{
            foreach($coupon as $k => $v){
                $coupon[$k]['start_time'] = date('Y-m-d',$v['start_time']);
                $coupon[$k]['end_time'] = date('Y-m-d',$v['end_time']);
            }
        }

        apiResponse('1','',$coupon);
    }

    /**
     * 清空优惠券
     */
    public function emptyCoupon($request = array()){
        $member = $this ->searchMember($request['token']);
        $coupon_id = explode(',',$request['coupon_id']);
        $where = array('id'=>array('IN',$coupon_id),'m_id'=>$member['id'],'status'=>array('neq',9));
        $data['status']  = 9;
        $data['update_time'] = time();
        $result = $this ->easyMysql('MemberCoupon',2,$where,$data);
        if(!$result){
            apiResponse('0','清空优惠券失败');
        }
        apiResponse('1','清空成功');
    }

    /**
     * 红包列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id：m_id
     */
    public function redPackageList($request = array()){
        $member = $this ->searchMember($request['token']);
        $where  = array('m_id'=>$member['id'],'status'=>1);
        $field  = 'id as red_id, title, type, price, create_time';
        $order  = 'create_time desc';
        $res_package = $this ->easyMysql('RedPackage','4',$where,'',$field, $order, $request['p']);

        if(!$res_package){
            $res_package = array();
        }else{
            foreach($res_package as $k => $v){
                $res_package[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
            }
        }

        apiResponse('1','',$res_package);
    }

    /**
     * 我的积分详情
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id：m_id
     */
    public function myIntegral($request = array()){
        $member = $this ->searchMember($request['token']);
        $result['integral'] = $member['integral'];
        apiResponse('1', '', $result);
    }

    /**
     * 兑换优惠券列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id：m_id
     */
    public function exchangeList($request = array()){
        $where = array('status'=>1, 'end_time'=>array('gt',time()));
        $field = 'id as exchange_id, satisty_price, discount_price, integral, start_time, end_time';
        $order = 'end_time asc, create_time desc';
        $exchange = $this ->easyMysql('Coupon','4',$where,'',$field,$order,$request['p']);
        if(!$exchange){
            $exchange = array();
        }else{
            foreach($exchange as $k =>$v){
                $exchange[$k]['start_time'] = date('Y-m-d',$v['start_time']);
                $exchange[$k]['end_time']   = date('Y-m-d',$v['end_time']);
            }
        }

        apiResponse('1','',$exchange);
    }

    /**
     * 积分兑换优惠券
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id：m_id
     */
    public function exchangeCoupon($request = array()){
        $member = $this ->searchMember($request['token']);
        $where  = array('id'=>$request['exchange_id'], 'status'=>1, 'end_time'=>array('gt',time()));
        $exchange = $this ->easyMysql('Coupon','3',$where);
        if(!$exchange){
            apiResponse('0','优惠券信息有误');
        }
        $where  = array('m_id'=>$member['id'],'coupon_id'=>$request['exchange_id'],'status'=>1);
        $member_coupon = $this ->easyMysql('MemberCoupon','3',$where);
        if($member_coupon){
            apiResponse('0','您已兑换该优惠券');
        }
        if($member['integral'] < $exchange['integral']){
            apiResponse('0','您的积分不足');
        }
        $data['m_id']           = $member['id'];
        $data['coupon_id']      = $request['exchange_id'];
        $data['satisty_price']  = $exchange['satisty_price'];
        $data['discount_price'] = $exchange['discount_price'];
        $data['start_time']     = $exchange['start_time'];
        $data['end_time']       = $exchange['end_time'];
        $data['create_time']    = time();
        $result = $this ->easyMysql('MemberCoupon','1','',$data);
        if(!$result){
            apiResponse('0','兑换优惠券失败');
        }
        $res = $this ->setType('Member',array('id'=>$member['id']),'integral',$exchange['integral'],2);

        apiResponse('1','兑换成功');
    }
}