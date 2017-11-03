<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class NewsController
 * @package Api\Controller
 * 大师服务订单模块
 */
class ServiceController extends BaseController{
    /**
     * 初始化
     */

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 大师服务订单列表
     * 大师token       token
     * 订单类型        type  1 待回复 2 进行中 4 已完成 3 全部
     * 分页参数        p
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

        D('Service','Logic') ->serviceOrderList(I('post.'));
    }

    /**
     * 大师服务订单详情
     * 大师token      token
     * 服务订单       sorder_id
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
        D('Service','Logic') ->serviceOrderInfo(I('post.'));
    }

    /**
     * 聊天
     * 大师token    token
     * 服务订单id   sorder_id
     */
    public function chat(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if(!$_POST['sorder_id']){
            apiResponse('0','服务订单ID不能为空');
        }
        D('Service','Logic') ->chat(I('post.'));
    }

    /**
     * 删除订单
     * 大师token    token
     * 服务订单id   sorder_id
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
        D('Service','Logic') ->deleteOrder(I('post.'));
    }

    /**
     * 同意退款或者拒绝退款
     * 大师token    token
     * 服务订单id   sorder_id
     * 类型   type  1  同意退款  2  拒绝退款
     * 退款金额     price
     */
    public function refundType(){
        //大师token不能为空
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //服务订单id不能为空
        if(!$_POST['sorder_id']){
            apiResponse('0','服务订单ID不能为空');
        }
        //类型不能有误
        if($_POST['type'] != 1&&$_POST['type'] != 2){
            apiResponse('0','操作方法不能为空');
        }
        //金额不能为空
        if(!$_POST['price'] ){
            apiResponse('0','退款金额不能为空');
        }
        D('Service','Logic') ->refundType(I('post.'));
    }

    /**
     * 退款详情
     */
    public function refundPage(){
        if(!$_POST['sorder_id']){
            apiResponse('0','服务订单ID不能为空');
        }
        D('Service','Logic') ->refundPage(I('post.'));
    }
}
