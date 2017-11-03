<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class MemberController
 * @package Api\Controller
 * 大师订单模块
 */
class MasterOrderController extends BaseController{
    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 商品订单列表
     * 大师token   token
     * 订单类型    type    类型值只能在0-4之间  0  待支付  1  待发货  2  待收货  3  待评价  4  已完成  5  全部
     * 分页参数    p
     */
    public function orderList(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //类型值只能在0-4之间  0  待支付  1  待发货  2  待收货  3  待评价  4  全部
        if($_POST['type']!=0 && $_POST['type']!=1 && $_POST['type']!=2 && $_POST['type']!=3 && $_POST['type']!=4 && $_POST['type']!=5 ){
            apiResponse('0','类型错误');
        }
        //分页参数不能为空
        if(empty($_POST['p'])){
            apiResponse('0','分页参数错误');
        }
        D('MasterOrder','Logic') ->orderList(I('post.'));
    }

    /**
     * 商品订单详情
     * 大师token   token
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

        D('MasterOrder','Logic') ->orderInfo(I('post.'));
    }

    /**
     * 商家发货
     * 大师token     token
     * 订单id        order_id
     */
    public function deliverGoods(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //订单id
        if(!$_POST['order_id']){
            apiResponse('0','订单id不能为空');
        }
        //物流公司id
        if(!$_POST['delivery_id']){
            apiResponse('0','物流公司id不能为空');
        }
        //物流单号
        if(!$_POST['delivery_sn']){
            apiResponse('0','物流单号不能为空');
        }
        D('MasterOrder','Logic') ->deliverGoods(I('post.'));
    }

    /**
     * 删除订单
     * 大师token     token
     * 订单id        order_id
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
        D('MasterOrder','Logic') ->deleteOrder(I('post.'));
    }

    /**
     * 协商退货界面
     * 大师token     token
     * 订单id        order_id
     */
    public function refundOrderPage(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //订单id
        if(!$_POST['order_id']){
            apiResponse('0','订单id不能为空');
        }
        D('MasterOrder','Logic') ->refundOrderPage(I('post.'));
    }

    /**
     * 取消退货同意退货
     * 大师token     token
     * 订单id        order_id
     * 操作类型      type    1  同意退货  2  拒绝退货
     */
    public function typeApply(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //订单id
        if(!$_POST['order_id']){
            apiResponse('0','订单id不能为空');
        }

        if($_POST['type'] != 1&&$_POST['type'] != 2){
            apiResponse('0','操作有误');
        }
        D('MasterOrder','Logic') ->typeApply(I('post.'));
    }

    /**
     * 填写退货地址
     * 大师token   token
     * 订单id      order_id
     * 收货人信息  people_name
     * 联系电话    telephone
     * 收货地址    address
     */
    public function returnAddress(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //订单id
        if(!$_POST['order_id']){
            apiResponse('0','订单id不能为空');
        }
        //收货人信息
        if(!$_POST['people_name']){
            apiResponse('0','收货人信息不能为空');
        }
        //联系电话
        if(!$_POST['telephone']){
            apiResponse('0','联系电话不能为空');
        }
        //收货地址
        if(!$_POST['address']){
            apiResponse('0','收货地址不能为空');
        }
        D('MasterOrder','Logic') ->returnAddress(I('post.'));
    }

    /**
     * 卖家确认收货
     * 大师token   token
     * 订单id      order_id
     */
    public function confirmGoods(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //订单id
        if(!$_POST['order_id']){
            apiResponse('0','订单id不能为空');
        }
        D('MasterOrder','Logic') -> confirmGoods(I('post.'));
    }
}
