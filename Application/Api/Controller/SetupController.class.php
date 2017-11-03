<?php
namespace Api\Controller;
/**
 * Class SetupController
 * @package Api\Controller
 * 设置模块  用户大师通用
 */
class SetupController extends BaseController{
    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 大师—设置
     */
    public function masterSetup(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Setup','Logic') ->masterSetup(I('post.'));
    }

    /**
     * 大师—红包范围
     */
    public function redRange(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['min_red'])){
            apiResponse('0','请填写最小红包值');
        }
        if(empty($_POST['max_red'])){
            apiResponse('0','请填写最大红包值');
        }
        D('Setup','Logic') ->redRange(I('post.'));
    }

    /**
     * 通用版—意见反馈
     * 用户token   token
     * 用户类型    user_type  1  用户  2  大师
     * 反馈类型    type       1  订单类型  2  其他类型
     * 反馈内容    content
     * 联系方式    telephone
     * 订单号      order_sn
     */
    public function feedBack(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if($_POST['user_type'] != 1&&$_POST['user_type'] != 2){
            apiResponse('0','用户类型有误');
        }
        if($_POST['type'] != 1&&$_POST['type'] != 2){
            apiResponse('0','反馈类型有误');
        }
        if(!$_POST['content']){
            apiResponse('0','请填写反馈内容');
        }
        if(!$_POST['telephone']){
            apiResponse('0','请填写联系方式');
        }

        D('Setup','Logic') ->feedBack(I('post.'));
    }

    /**
     * 关于我们
     * 传递参数的方式：post
     * 需要传递的参数：无
     */
    public function aboutUs(){
        D('Setup','Logic')->aboutUs(I('post.'));
    }

    /**
     * 分享页面
     * 传递参数的方式：post
     * 用户token    token
     * 用户类型     user_type
     */
    public function sharePage(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if($_POST['user_type'] != 1&&$_POST['user_type'] != 2){
            apiResponse('0','用户类型有误');
        }
        D('Setup','Logic')->sharePage(I('post.'));
    }
}