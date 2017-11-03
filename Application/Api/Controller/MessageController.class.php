<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class MessageController
 * @package Api\Controller
 * 信息模块
 */
class MessageController extends BaseController{
    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 服务列表
     * 用户类型  user_type  1  用户端  2  大师端
     * 环信json  easemob_json[{"easemob_account":"150131831611157"},{"easemob_account":"150172352741897"}]
     */
    public function serviceMessage(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if($_POST['user_type'] != 1&&$_POST['user_type'] != 2){
            apiResponse('0','用户类型有误');
        }
        D('Message','Logic') ->serviceMessage(I('post.'));
    }

    /**
     * 消息列表
     * 用户token   token
     * 用户类型    user_type  1  用户  2  大师
     * 信息类型    type  2  悬赏订单  3  宝物订单  4  系统订单
     * 分页参数    p
     */
    public function messageList(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if($_POST['user_type'] != 1&&$_POST['user_type'] != 2){
            apiResponse('0','用户类型有误');
        }
        if($_POST['type'] == 2 && $_POST['type'] == 3 && $_POST['type'] == 4){
            apiResponse('0','信息类型有误');
        }
        if(!$_POST['p']){
            apiResponse('0','分页参数不能为空');
        }
        D('Message','Logic') ->messageList(I('post.'));
    }

    /**
     * 系统消息详情
     */
    public function messageInfo(){
        if(!$_POST['message_id']){
            apiResponse('0','信息ID不能为空');
        }
        D('Message','Logic') ->messageInfo(I('post.'));
    }

    /**
     * 随缘红包
     * 用户token  token
     * 大师ID     master_id
     * 红包金额   price
     */
    public function revelPrice(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(!$_POST['master_id']){
            apiResponse('0','大师ID不能为空');
        }
        D('Message','Logic') ->revelPrice(I('post.'));
    }

    /**
     * 大师打开红包
     * 大师token   token
     * 红包ID      red_id
     */
    public function openPrice(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(!$_POST['red_id']){
            apiResponse('0','红包ID不能为空');
        }
        D('Message','Logic') ->openPrice(I('post.'));
    }

    /**
     * 摇卦卜卦
     */
    public function fortuneTelling(){
        D('Message','Logic') ->fortuneTelling(I('post.'));
    }

    /**
     * 支付红包页面
     * 用户token      token
     * 红包ID         red_id
     * 红包编号       order_sn
     * 红包金额       price
     */
    public function redPayPage(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(!$_POST['red_id']){
            apiResponse('0','红包ID不能为空');
        }
        if(!$_POST['order_sn']){
            apiResponse('0','红包编号不能为空');
        }
        if(!$_POST['price']){
            apiResponse('0','红包金额不能为空');
        }
        D('Message','Logic') ->redPayPage(I('post.'));
    }
}
