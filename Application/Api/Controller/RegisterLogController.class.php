<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class RegisterLogController
 * @package Api\Controller
 * 登录注册模块
 */
class RegisterLogController extends BaseController{
    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }
    /*
     * 发送验证码
     * 手机账号    account
     * 发送类型    type  retrieve  找回  activate  注册  bind  解除绑定  newbind  重新绑定
     * 用户类型    user_type  1  用户  2  大师
     */
    public function sendVerify(){
        //邮箱账号不能为空
        if(empty($_POST['account'])){
            apiResponse('0','请输入账号');
        }

        //判断手机格式
        if(!preg_match(C('MOBILE'), $_POST['account'])) {
            apiResponse('0','手机格式有误');
        }
        //发送类型不能有误  retrieve  找回  activate  注册  bind  解除绑定  newbind  重新绑定
        if($_POST['type']!='retrieve'&&$_POST['type']!='activate'&&$_POST['type']!='bind'&&$_POST['type']!='new_bind'&&$_POST['type']!='auth'){
            apiResponse('0','发送类型有误');
        }
        //用户类型  user_type 1  用户类型  2  大师类型
        if($_POST['user_type'] != 1 && $_POST['user_type'] != 2){
            apiResponse('0','用户类型有误');
        }
        D('RegisterLog','Logic')->sendVerify(I('post.'));
    }
    /*
     * 用户注册
     * 手机账号    account
     * 验证码      verify
     * 登录密码    password
     * 再次输入密码  sec_password
     * 用户类型    user_type
     * 所在经度    lat
     * 所在纬度    lng
     */
    public function  register(){
        //邮箱账号不能为空
        if(empty($_POST['account'])){
            apiResponse('0','请输入账号');
        }
        //判断手机格式
        if(!preg_match(C('MOBILE'),$_POST['account'])) {
            apiResponse('0','手机格式有误');
        }
        //登录密码不能为空
        if(empty($_POST['password'])){
            apiResponse('0','请输入密码');
        }
        //登录密码不能为空
        if(empty($_POST['sec_password'])){
            apiResponse('0','请再次输入密码');
        }
        $num = strlen($_POST['password']);
        if($num < 6){
            apiResponse('0','密码长度不得小于6位');
        }
        if($_POST['password'] != $_POST['sec_password']){
            apiResponse('0','两次密码输入不一致');
        }
        //用户类型  user_type 1  用户类型  2  大师类型
        if($_POST['user_type'] != 1 && $_POST['user_type'] != 2){
            apiResponse('0','用户类型有误');
        }
        if(empty($_POST['verify'])){
            apiResponse('0','验证码不能为空');
        }
        D('RegisterLog','Logic') ->register(I('post.'));
    }
    /*
      * 用户登录
      * 用户账号    account
      * 用户密码    password
      * 用户类型    user_type  1  用户  2  大师
      * 所在经度    lat
      * 所在纬度    lng
      */
    public function  Login(){
        //登录账号不能为空
        if(empty($_POST['account'])){
            apiResponse('0','请输入账号');
        }
        //判断邮箱格式
        if(!preg_match(C('MOBILE'),$_POST['account'])) {
            apiResponse('0','手机格式有误');
        }
        //用户密码不能为空
        if(empty($_POST['password'])){
            apiResponse('0','用户密码不能为空');
        }
        //用户类型  user_type 1  用户类型  2  大师类型
        if($_POST['user_type'] != 1 && $_POST['user_type'] != 2){
            apiResponse('0','用户类型有误');
        }
        D('RegisterLog','Logic') ->Login(I('post.'));
    }
    /*
     * 三方登录
     * 三方账号   open_id
     * 三房类型   open_type
     * 用户类型   user_type
     * 账号昵称   nickname
     * 账号头像   head_pic
     */
    public function  thirdLogin(){
        //三方账号不能为空
        if(!$_POST['open_id']){
            apiResponse('0','三方账号不能为空');
        }
        //三方账号类型不能为空  1  微信  2  QQ  3  新浪微博
        if(!$_POST['open_type']){
            apiResponse('0','三方账号类型不能为空');
        }
        //用户类型不能为空  1  用户  2  大师
        if($_POST['user_type'] != 1&&$_POST['user_type'] != 2){
            apiResponse('0','用户类型不能为空');
        }
        //三方昵称不能为空
        if(!$_POST['nickname']){
            apiResponse('0','三方昵称不能为空');
        }
        D('RegisterLog','Logic') ->thirdLogin(I('post.'));
    }
    /*
     * 忘记密码页面
     * 用户账号   account
     * 验证码     verify
     * 密码       password
     * 二次密码   sec_password
     * 用户类型   user_type
     */
    public function  forgetPassword(){
        if(empty($_POST['account'])){
            apiResponse('0','请填写账号');
        }
        //判断手机格式
        if(!preg_match(C('MOBILE'),$_POST['account'])) {
            apiResponse('0','手机格式有误');
        }
        if(empty($_POST['verify'])){
            apiResponse('0','请填写验证码');
        }
        if(empty($_POST['password'])){
            apiResponse('0','请填写密码');
        }
        if(empty($_POST['sec_password'])){
            apiResponse('0','请再次填写密码');
        }
        if($_POST['user_type'] != 1&&$_POST['user_type'] != 2){
            apiResponse('0','用户类型有误');
        }
        $num = strlen($_POST['password']);
        if($num < 6){
            apiResponse('0','密码长度不得小于6位');
        }
        if($_POST['password'] != $_POST['sec_password']){
            apiResponse('0','两次密码输入不一致');
        }

        D('RegisterLog','Logic') ->forgetPassword(I('post.'));
    }
    /**
     * 三方登录绑定账号
     * 用户手机号  account
     * 验证码      verify
     * 三方ID      o_id
     * 用户类型    user_type  1  用户  2  大师
     * 所在经度    lat
     * 所在纬度    lng
     */
    public function thirdBindAccount(){
        if(empty($_POST['account'])){
            apiResponse('0','请填写手机号');
        }
        if(empty($_POST['verify'])){
            apiResponse('0','请填写验证码');
        }
        //判断手机格式
        if(!preg_match(C('MOBILE'),$_POST['account'])) {
            apiResponse('0','手机格式有误');
        }
        if(empty($_POST['o_id'])){
            apiResponse('0','三方ID不能为空');
        }
        if($_POST['user_type'] != 1 &&$_POST['user_type'] != 2){
            apiResponse('0','用户类型有误');
        }
        D('RegisterLog','Logic') ->thirdBindAccount(I('post.'));
    }
    /**
     * 测试短信
     */
    public function testVerify(){
        D('RegisterLog','Logic') ->testVerify(I('post.'));
    }

    public function download(){
        header("location:http://master.txunda.com");exit;
    }

    /**
     * 大师或者用户设置密码
     * 用户token     token      adf962e22137a8860ddf1f81bcc9c094
     * 用户类型      user_type  1  用户  2  大师
     * 用户密码      password
     * 再次输入密码  sec_password
     */
    public function setPassword(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if($_POST['user_type'] != 1 &&$_POST['user_type'] != 2){
            apiResponse('0','用户类型有误');
        }
        //登录密码不能为空
        if(empty($_POST['password'])){
            apiResponse('0','请输入密码');
        }
        //登录密码不能为空
        if(empty($_POST['sec_password'])){
            apiResponse('0','请再次输入密码');
        }
        $num = strlen($_POST['password']);
        if($num < 6){
            apiResponse('0','密码长度不得小于6位');
        }
        if($_POST['password'] != $_POST['sec_password']){
            apiResponse('0','两次密码输入不一致');
        }
        D('RegisterLog','Logic') ->setPassword(I('post.'));
    }
}
