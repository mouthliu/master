<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class CouponController
 * @package Api\Controller
 * 优惠券模块
 */
class CouponController extends BaseController{
    /**
     * 初始化
     */
    public function _initialize(){

    }

    /**
     * 优惠券列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户token：token
     * 类型：type=1 可以使用的优惠券，2已过期  3已使用的优惠券
     * 分页参数   p
     */
    public function couponList(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if($_POST['type'] != 1&&$_POST['type'] != 2&&$_POST['type'] != 3){
            apiResponse('0','优惠券类型有误');
        }
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        D('Coupon','Logic')->couponList(I('post.'));
    }

    /**
     * 清空优惠券
     * 用户token    token
     * 优惠券ID     coupon_id
     */
    public function emptyCoupon(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['coupon_id'])){
            apiResponse('0','优惠券ID不能为空');
        }
        D('Coupon','Logic')->emptyCoupon(I('post.'));
    }

    /**
     * 红包列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户token： token
     * 分页参数    p
     */
    public function redPackageList(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        D('Coupon','Logic')->redPackageList(I('post.'));
    }

    /**
     * 我的积分详情
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户id：m_id
     */
    public function myIntegral(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Coupon','Logic')->myIntegral(I('post.'));
    }

    /**
     * 积分兑换优惠券
     * 传递参数的方式：post
     * 需要传递的参数：
     * 分页参数：p
     */
    public function exchangeList(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(!$_POST['p']){
            apiResponse('0','分页参数不能为空');
        }
        D('Coupon','Logic')->exchangeList(I('post.'));
    }

    /**
     * 积分兑换优惠券
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户token：  token
     * 兑换优惠券ID exchange_id
     */
    public function exchangeCoupon(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if(!$_POST['exchange_id']){
            apiResponse('0','优惠券ID不能为空');
        }
        D('Coupon','Logic')->exchangeCoupon(I('post.'));
    }

}
