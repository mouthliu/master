<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class MemberController
 * @package Api\Controller
 * 用户订单模块
 */
class MemberOrderController extends BaseController{
    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 单品提交订单页面
     * 用户token   token
     * 商品id      goods_id
     * 购买数量    num
     * 用户备注    remark
     */
    public function submitOrderPage(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(!$_POST['goods_id']){
            apiResponse('0','商品id不能为空');
        }
        //购买数量不能为空
        if(empty($_POST['num'])){
            apiResponse('0','请填写购买数量');
        }
        D('MemberOrder','Logic') ->submitOrderPage(I('post.'));
    }

    /**
     * 单品提交订单
     * 用户token   token
     * 商品id      goods_id
     * 地址id      address_id
     * 购买数量    num
     */
    public function submitOrder(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(!$_POST['goods_id']){
            apiResponse('0','商品id不能为空');
        }
        if(!$_POST['address_id']){
            apiResponse('0','请选择地址');
        }
        //购买数量不能为空
        if(empty($_POST['num'])){
            apiResponse('0','请填写购买数量');
        }
        D('MemberOrder','Logic') ->submitOrder(I('post.'));
    }

    /**
     * 购物车提交订单页面
     * 用户token     token
     * 购物车id      cart_json
     */
    public function submitShopCartPage(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //购物车信息
        if (empty($_POST['cart_json'])) {
            apiResponse('0', '请选择要买的商品');
        }
        D('MemberOrder','Logic') ->submitShopCartPage(I('post.'));
    }

    /**
     * 购物车提交订单
     * 用户token     token
     * 地址id        address_id
     * 购物车信息    cart_json       json串：[{"cart_id":"4"},{"cart_id":"10"}]
     * 优惠券信息    coupon_json     [{"master_id":"2","coupon_id":"1"},{"master_id":"1","coupon_id":"2"}]
     * 买家留言      message_json    [{"master_id":"2","message":"这是第一个"},{"master_id":"1","message":"这是第二个"}]
     */
    public function submitShopCart(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //购物车信息
        if (empty($_POST['cart_json'])) {
            apiResponse('0', '请选择要买的商品');
        }
        if(!$_POST['address_id']){
            apiResponse('0','请选择地址');
        }
        D('MemberOrder','Logic') ->submitShopCart(I('post.'));
    }

    /**
     * 商品订单列表
     * 用户token    token
     * 类型值   type     0  待支付  1  待发货  2  待收货  3  待评价  4  已完成  5  全部
     * 分页参数     p
     */
    public function orderList(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //类型值只能在0-4之间  0  待支付  1  待发货  2  待收货  3  待评价  4  已完成  5  全部
        if($_POST['type']!=0 && $_POST['type']!=1 && $_POST['type']!=2 && $_POST['type']!=3 && $_POST['type']!=4 && $_POST['type']!= 5){
            apiResponse('0','类型错误');
        }
        //分页参数不能为空
        if(empty($_POST['p'])){
            apiResponse('0','分页参数错误');
        }
        D('MemberOrder','Logic') ->orderList(I('post.'));
    }

    /**
     * 商品订单详情
     * 用户token   token
     * 订单id      order_id
     */
    public function orderInfo(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //订单id
        if(!$_POST['order_id']){
            apiResponse('0','订单id不能为空');
        }

        D('MemberOrder','Logic') ->orderInfo(I('post.'));
    }

    /**
     * 取消订单
     * 用户token    token
     * 订单id       order_id
     */
    public function cancellOrder (){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //订单id
        if(!$_POST['order_id']){
            apiResponse('0','订单id不能为空');
        }
        D('MemberOrder','Logic') ->cancellOrder(I('post.'));
    }

    /**
     * 确认收货
     * 用户token    token
     * 订单id       order_id
     */
    public function confirmOrder (){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //订单id
        if(!$_POST['order_id']){
            apiResponse('0','订单id不能为空');
        }
        D('MemberOrder','Logic') ->confirmOrder(I('post.'));
    }

    /**
     * 评价订单
     * 用户token    token
     * 订单id       order_id
     */
    public function evaluateOrder (){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if(!$_POST['order_id']){
            apiResponse('0','订单id不能为空');
        }
        D('MemberOrder','Logic') ->evaluateOrder(I('post.'));
    }

    /**
     * 上传图片
     */
    public function evaluatePicture (){
        D('MemberOrder','Logic') ->evaluatePicture(I('post.'));
    }

    /**
     * 删除订单
     * 用户token   token
     * 订单id      order_id
     */
    public function deleteOrder (){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //订单id
        if(!$_POST['order_id']){
            apiResponse('0','订单id不能为空');
        }
        D('MemberOrder','Logic') ->deleteOrder(I('post.'));
    }

    /**
     * 申请退货
     * 用户token   token
     * 订单id      order_id
     * 售后类型    customer_type   1  我要退款  2  我要退货
     * 货物状态    goods_type      1  已收到货  2  未收到货
     * 原因id      reason
     * 退款金额    price
     * 退款说明    content
     * 多图上传    picture
     */
    public function refundOrder (){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(!$_POST['order_id']){
            apiResponse('0','订单id不能为空');
        }
        if($_POST['customer_type']!=1&&$_POST['customer_type']!=2){
            apiResponse('0','售后类型不能为空');
        }
        if($_POST['goods_type']!=1&&$_POST['goods_type']!=2){
            apiResponse('0','货物状态不能为空');
        }
        if(!$_POST['reason']){
            apiResponse('0','原因id不能为空');
        }
        if(!$_POST['price']){
            apiResponse('0','退款金额不能为空');
        }
        if(!$_POST['content']){
            apiResponse('0','退款说明不能为空');
        }
        D('MemberOrder','Logic') ->refundOrder(I('post.'));
    }

    /**
     * 取消申请
     * 用户token   token
     * 订单id      order_id
     */
    public function cancellRefund (){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(!$_POST['order_id']){
            apiResponse('0','订单id不能为空');
        }
        D('MemberOrder','Logic') ->cancellRefund(I('post.'));
    }

    /**
     * 填写运单信息
     * 用户token    token
     * 订单id       order_id
     * 快递id       delivery
     * 快递单号     number
     */
    public function chooseExpress(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(!$_POST['order_id']){
            apiResponse('0','订单id不能为空');
        }
        if(!$_POST['delivery']){
            apiResponse('0','快递类型不能为空');
        }
        if(!$_POST['number']){
            apiResponse('0','快递单号不能为空');
        }
        D('MemberOrder','Logic') ->chooseExpress(I('post.'));
    }

    /**
     * 快递列表
     */
    public function expressList(){
        D('MemberOrder','Logic') -> expressList(I('post.'));
    }

    /**
     * 协商退货界面
     * 用户token    token
     * 订单id       order_id
     */
    public function refundOrderPage(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(!$_POST['order_id']){
            apiResponse('0','反馈id不能为空');
        }
        D('MemberOrder','Logic') ->refundOrderPage(I('post.'));
    }

    /**
     * 订单支付页面
     * 用户token    token
     * 订单id       order_id
     */
    public function orderListPage(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(!$_POST['order_id']){
            apiResponse('0','反馈id不能为空');
        }
        D('MemberOrder','Logic') ->orderListPage(I('post.'));
    }
}
