<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class NewsController
 * @package Api\Controller
 * 用户服务订单模块
 */
class ServiceOrderController extends BaseController{
    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 服务订单列表
     * 用户token   token
     * 订单类型    1  待回复  2  进行中  4  已完成  3  全部
     * 分页参数    p
     */
    public function serviceOrderList(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if($_POST['type'] != 1&&$_POST['type'] != 2&&$_POST['type'] != 3&&$_POST['type'] != 4){
            apiResponse('0','订单类型有误');
        }

        if(!$_POST['p']){
            apiResponse('0','分页参数不能为空');
        }

        D('ServiceOrder','Logic') ->serviceOrderList(I('post.'));
    }

    /**
     * 服务订单详情
     * 用户token    token
     * 服务订单id   sorder_id
     */
    public function serviceOrderInfo(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if(!$_POST['sorder_id']){
            apiResponse('0','服务订单ID不能为空');
        }
        D('ServiceOrder','Logic') ->serviceOrderInfo(I('post.'));
    }

    /**
     * 取消订单
     * 用户token    token
     * 服务订单id   sorder_id
     */
    public function cancelOrder(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if(!$_POST['sorder_id']){
            apiResponse('0','服务订单ID不能为空');
        }

        D('ServiceOrder','Logic') ->cancelOrder(I('post.'));
    }

    /**
     * 申请退款
     * 用户token   token
     * 服务订单id  sorder_id
     * 原因id      reason_id
     * 退款金额    price
     * 退款说明    content
     * 多图上传    picture
     */
    public function applyRefund(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if(!$_POST['sorder_id']){
            apiResponse('0','服务订单ID不能为空');
        }

        if(!$_POST['reason_id']){
            apiResponse('0','原因ID不能为空');
        }

        if(!$_POST['price']){
            apiResponse('0','退款金额不能为空');
        }

        if(!$_POST['content']){
            apiResponse('0','退款说明不能为空');
        }

        D('ServiceOrder','Logic') ->applyRefund(I('post.'));
    }

    /**
     * 等待评价
     * 用户token    token
     * 服务订单id   sorder_id
     * 评论星级     rank
     * 评论内容     content
     * 上传图片     picture
     */
    public function evaluateOrder(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if(!$_POST['sorder_id']){
            apiResponse('0','服务订单ID不能为空');
        }

        if(!$_POST['rank']){
            apiResponse('0','评论星级不能为空');
        }

        if(!$_POST['content']){
            apiResponse('0','评论内容不能为空');
        }

        if(!$_POST['anonymous']){
            apiResponse('0','匿名状态不能为空');
        }
        D('ServiceOrder','Logic') ->evaluateOrder(I('post.'));
    }

    /**
     * 删除订单
     * 用户token     token
     * 服务订单      sorder_id
     */
    public function deleteOrder(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if(!$_POST['sorder_id']){
            apiResponse('0','服务订单ID不能为空');
        }

        D('ServiceOrder','Logic') ->deleteOrder(I('post.'));
    }

    /**
     * 协商退货页面
     * 用户token     token
     * 服务订单id    sorder_id
     */
    public function refundPage(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if(!$_POST['sorder_id']){
            apiResponse('0','服务订单ID不能为空');
        }

        D('ServiceOrder','Logic') ->refundPage(I('post.'));
    }

    /**
     * 评价服务订单页面
     * 服务订单id   sorder_id
     */
    public function evaluateOrderPage(){
        if(!$_POST['sorder_id']){
            apiResponse('0','服务订单ID不能为空');
        }
        D('ServiceOrder','Logic') ->evaluateOrderPage(I('post.'));
    }

    /**
     * 退货原因列表
     */
    public function reasonList(){
        D('ServiceOrder','Logic') ->reasonList(I('post.'));
    }

    /**
     * 取消退款
     * 用户token   token
     * 服务订单id  sorder_id
     */
    public function cancellRefund(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if(!$_POST['sorder_id']){
            apiResponse('0','服务订单ID不能为空');
        }

        D('ServiceOrder','Logic') ->cancellRefund(I('post.'));
    }
}
