<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class MemberController
 * @package Api\Controller
 * 大师模块
 */
class MasterController extends BaseController{

    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }

    /*
    * 大师中心
    * 用户ID   账号token
    */
    public function  masterCenter(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Master','Logic') ->masterCenter(I('post.'));
    }

    /*
    * 大师余额
    * 用户ID   账号token
    */
    public function  masterbalance(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Master','Logic') ->masterbalance(I('post.'));
    }

    /*
    * 大师提现页
    * 用户ID   账号token
    */
    public function  masterWithdrawPage(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Master','Logic') ->masterWithdrawPage(I('post.'));
    }

    /*
    * 大师提现
     * 大师标识        token
     * 提现金额        price
     * 提现银行卡ID    bank_id
    */
    public function  masterWithdraw(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if($_POST['type'] != 1&&$_POST['type'] != 2){
            apiResponse('0','提现类型有误');
        }

        if($_POST['price'] < 0.01){
            apiResponse('0','提现金额有误');
        }
        if($_POST['type'] == 1){
            if(empty($_POST['bank_id'])){
                apiResponse('0','请选择银行卡');
            }
        }else{
            if(empty($_POST['alipay_account'])){
                apiResponse('0','请填写支付宝账号');
            }
            if(empty($_POST['alipay_name'])){
                apiResponse('0','请填写支付宝姓名');
            }
        }
        D('Master','Logic') ->masterWithdraw(I('post.'));
    }

    /*
    * 大师明细
     * 用户ID   账号token
     * 分页参数  p
    */
    public function  masterDetail(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        D('Master','Logic') ->masterDetail(I('post.'));
    }

    /*
    * 服务列表
    * 用户ID   账号token
    */
    public function  serviceManage(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Master','Logic') ->serviceManage(I('post.'));
    }

    /*
    * 编辑服务列表
    * 用户ID   账号token
    */
    public function serviceModify(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Master','Logic') ->serviceModify(I('post.'));
    }

    /*
    * 大师资料
    * 用户ID   账号token
    */
    public function masterInfo(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Master','Logic') ->masterInfo(I('post.'));
    }

    /*
    * 修改大师资料
    * 用户ID   账号token
    */
    public function masterModify(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Master','Logic') ->masterModify(I('post.'));
    }

    /*
    * 擅长领域列表
    * 用户ID   账号token
    */
    public function fieldList(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Master','Logic') ->fieldList(I('post.'));
    }

    /*
    * 大师认证
     * 用户ID   账号token
     * 真实姓名    name
     * 身份证号   idcard
     * 手机号码   phone
     * 身份证正面照   front_idcard
     * 身份证背面照   back_idcard
     * 手持身份证照   hand_idcard
     * 店铺照片       shop_pic
    */
    public function Authentication(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['name'])){
            apiResponse('0','请填写真实姓名');
        }
        if(empty($_POST['idcard'])){
            apiResponse('0','请填写身份证号');
        }
        if(empty($_POST['phone'])){
            apiResponse('0','请填写手机号码');
        }
        if(empty($_POST['verify'])){
            apiResponse('0','请输入验证码');
        }
        D('Master','Logic') ->Authentication(I('post.'));
    }

    /*
    * 账户明细
    * 用户token   账号token
     * 明细状态  1  服务  2  商品  3  悬赏  4  全部
    */
    public function payLog(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if($_POST['type'] != 1&&$_POST['type'] != 2&&$_POST['type'] != 3&&$_POST['type'] != 4){
            apiResponse('0','明细状态有误');
        }
        if($_POST['time_type'] != 1&&$_POST['time_type'] != 2){
            apiResponse('0','时间类别有误');
        }
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        D('Master','Logic') ->payLog(I('post.'));
    }

    /*
     * 评价列表
     * 大师token   token
     * 评价类型    type  1  商品评价  2  服务订单
     * 分页参数    p
    */
    public function commentList(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if($_POST['type'] != 1&&$_POST['type'] != 2){
            apiResponse('0','评价类型');
        }
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        D('Master','Logic') ->commentList(I('post.'));
    }

    /**************************一道华丽的分割线*****************************/

    /**
     * 设置页
     * 大师的token
     */
    public function setupPage(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Master','Logic') ->setupPage(I('post.'));
    }

    /*
    * 绑定新手机号第一步
    * 用户token   token
    * 验证码      verify
    * 用户账号   account
    */
    public function  bindPhoneOne(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //验证码不能为空
        if(empty($_POST['account'])){
            apiResponse('0','验证账号不能为空');
        }
        //验证码不能为空
        if(empty($_POST['verify'])){
            apiResponse('0','验证码不能为空');
        }
        D('Master','Logic') ->bindPhoneOne(I('post.'));
    }
    /*
     * 绑定新手机号第二步
     * 用户token   token
     * 新手机号    account
     * 验证码      verify
    */
    public function  bindPhoneTwo(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //新手机号不能为空
        if(empty($_POST['account'])){
            apiResponse('0','新手机号不能为空');
        }
        //验证码不能为空
        if(empty($_POST['verify'])){
            apiResponse('0','验证码不能为空');
        }
        D('Master','Logic') ->bindPhoneTwo(I('post.'));
    }

    /*
    * 修改密码
     * 用户token        token
     * 原密码           password
     * 新密码           new_password
     * 再次输入新密码   sec_password
   */
    public function  Modifypassword(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //原密码不能为空
        if(empty($_POST['password'])){
            apiResponse('0','请输入原密码');
        }
        //新密码不能为空
        if(empty($_POST['new_password'])){
            apiResponse('0','请输入新密码');
        }
        //新密码不能为空
        if(empty($_POST['sec_password'])){
            apiResponse('0','请再次输入新密码');
        }
        $num = strlen($_POST['password']);
        if($num < 6){
            apiResponse('0','密码长度不得小于6位');
        }
        if($_POST['new_password'] != $_POST['sec_password']){
            apiResponse('0','两次密码输入不一致');
        }
        D('Master','Logic') ->Modifypassword(I('post.'));
    }

    /**
     * 先登录再绑定三方账号
     * 用户标识   token
     * 三方账号   open_id
     * 三房类型   open_type
     */
    public function bindThirdAccount(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['open_id'])){
            apiResponse('0','三方账号不能为空');
        }
        if($_POST['open_type'] != 1&&$_POST['open_type'] != 2&&$_POST['open_type'] != 3){
            apiResponse('0','三方类型不能为空');
        }
        if(empty($_POST['nickname'])){
            apiResponse('0','三方昵称不能为空');
        }
        D('Master','Logic') ->bindThirdAccount(I('post.'));
    }

    /**
     * 进跳转页
     */
    public function orderType(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //order_type  1  今日订单  2  新订单  3  未完成订单
        if($_POST['order_type'] != 1&&$_POST['order_type'] != 2&&$_POST['order_type'] != 3){
            apiResponse('0','订单类型不能为空');
        }
        D('Master','Logic') ->orderType(I('post.'));
    }

    /**
     * 获取支付宝账号
     */
    public function takeAlipay(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Master','Logic') ->takeAlipay(I('post.'));
    }
}
