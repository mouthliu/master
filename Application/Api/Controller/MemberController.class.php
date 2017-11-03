<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class MemberController
 * @package Api\Controller
 * 用户模块
 */
class MemberController extends BaseController{

    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }

    /*
    * 个人中心
    * 用户ID   账号token
    */
    public function  memberCenter(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Member','Logic') ->memberCenter(I('post.'));
    }

    /**
     * 设置页
     */
    public function setupPage(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Member','Logic') ->setupPage(I('post.'));
    }

    /*
    * 个人资料
    * 用户token    token
    */
    public function  memberInfo(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Member','Logic') ->memberInfo(I('post.'));
    }

    /*
    * 修改个人资料
    * 用户ID   m_id
    * 客户昵称 nickname
    * 上传图片
    */
    public function  modifyMemberInfo(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Member','Logic') ->modifyMemberInfo(I('post.'));
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
        D('Member','Logic') ->bindPhoneOne(I('post.'));
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
        D('Member','Logic') ->bindPhoneTwo(I('post.'));
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
        D('Member','Logic') ->Modifypassword(I('post.'));
    }

    /*
     * 我的钱包
     * 用户ID    m_id
     */
    public function myWallet(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Member','Logic') -> myWallet(I('post.'));
    }

    /**
     * 充值页面
     */
    public function rechargePage(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Member','Logic') -> rechargePage(I('post.'));
    }

    /*
    * 用户充值
    * 用户标识      token
     * 充值方式     ch_id
     * 充值类别     type  1  支付宝  2  微信  3  银行卡
     * 充值金额     price
    */
    public function recharge(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['ch_id'])&&empty($_POST['price'])){
            apiResponse('0','请选择充值金额');
        }
        //充值方式  1  支付宝  2  微信  3  银行卡
        if($_POST['type'] != 1&&$_POST['type'] != 2&&$_POST['type'] != 3){
            apiResponse('0','支付方式有误');
        }
        D('Member','Logic') -> recharge(I('post.'));
    }

    /*
    * 用户提现页面
    * 用户标识      token
    */
    public function withdrawPage(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Member','Logic') -> withdrawPage(I('post.'));
    }

    /*
    * 用户提现
     * 用户标识      token
     * 银行卡ID     bank_id
     * 提现金额     price
    */
    public function withdraw(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if($_POST['type'] != 1&&$_POST['type'] != 2){
            apiResponse('0','提现类型有误');
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

        if($_POST['price'] < 0.01){
            apiResponse('0','请输入正确的提现金额');
        }
        D('Member','Logic') -> withdraw(I('post.'));
    }

    /**
     * 账户明细
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户标识      token
     * 分页参数      p
     */
    public function detail(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }

        D('Member','Logic') -> detail(I('post.'));
    }

    /*
    * 红包管理
    * 用户标识      token
    */
    public function couponList(){
        D('Member','Logic') -> couponList(I('post.'));
    }

    /**
     * 获取支付宝支付参数
     * 传递参数的方式：post
     * 需要传递的参数：
     * 订单id：  recharge_id
     * 订单金额  price
     */
    public function getAlipayParam(){
        Vendor('Alipay.Alipay');
        if(!$_POST['recharge_id']){
            apiResponse('0','充值id不能为空');
        }
        if(!$_POST['price']){
            apiResponse('0','金额不能为空');
        }
        //查询订单信息
        $info = M('MemberRecharge')->where(array('id'=>$_POST['recharge_id']))->find();
        if(!$info){
            apiResponse('300','订单信息查询失败');
        }
        $url  = C('API_URL').'/index.php/Api/Member/alipayNotify';
        $order_sn = $info['order_sn'];
//        $pay_money = $_POST['price'];
        $pay_money = '0.01';
        //生成支付字符串
//        $notify_url   = C('API_URL').'/index.php/Api/Pay/alipayNotify';
        $notify_url   = $url;
        $out_trade_no = $order_sn;
        $total_amount = $pay_money;
        $signType     = 'RSA2';
        $payObject = new \Alipay($notify_url,$out_trade_no,$total_amount,$signType);
        $pay_string = $payObject->appPay();
        apiResponse('1','请求成功',array('pay_string'=>$pay_string));
    }

    /**
     * 支付宝回调
     */
    public function alipayNotify(){
        $out_trade_no = $_POST['out_trade_no'];
        $trade_status = $_POST['trade_status'];
        if($trade_status == 'TRADE_SUCCESS'){
            $where['order_sn'] =$out_trade_no;
            $where['status'] = array('eq',0);
            $recharge_info = D('Member','Logic') ->easyMysql('MemberRecharge','3',$where);
            if($recharge_info){
                //修改充值订单状态
                unset($where);
                $where['id'] = $recharge_info['id'];
                $data['update_time'] = time();
                $data['status']      = 1;
                $data['pay_status']  = 1;
                $data['pay_type']    = 1;
                $res = D('Member','Logic') ->easyMysql('MemberRecharge','2',$where,$data);
                if(!$res){
                    apiResponse('0','修改订单状态失败');
                }
                //添加用户余额
                unset($where);
                $result = M('Member')->where(array('id'=>$recharge_info['m_id']))->setInc('balance',$recharge_info['price']);
                if(!$result){
                    apiResponse('0','修改用户余额失败');
                }
                //添加账单明细
                $result_data = D('Member','Logic') ->addDetail(1, $recharge_info['m_id'], 1, '充值', 1, $recharge_info['price']);
                if(!$result_data){
                    apiResponse('0','添加账单明细失败');
                }
            }else{
                apiResponse('0','充值失败');
            }
        }else{
            apiResponse('0','支付宝余额不足');
        }

        apiResponse('1','充值成功');
    }

    /**
     * 获取微信支付参数
     * 传递参数的方式：post
     * 需要传递的参数：
     * 订单id：recharge_id
     */
    public function getWXPayParam(){
        Vendor('WxPayApp.lib.WxPay#Api');
        if(empty($_POST['recharge_id'])){
            apiResponse('0','参数不完整');
        }
        //查询订单信息
        $info = M('MemberRecharge')->where(array('id'=>$_POST['recharge_id']))->find();
        if(!$info){
            apiResponse('300','订单信息查询失败');
        }

        //②、统一下单
        $input = new \WxPayUnifiedOrder();
        $input->SetBody("用户充值");
        $input->SetAttach("用户充值");
        $input->SetOut_trade_no($info['order_sn']);
        $input->SetTotal_fee($info['price']*100);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("APP支付");
        $input->SetNotify_url("http://master.txunda.com/index.php/Api/Member/wXinNotify");
        $input->SetTrade_type("APP");
        $order = \WxPayApi::unifiedOrder($input);

        $time = time().'';
        $sign_data['appid']     = $order['appid'];
        $sign_data['mch_id']    = $order['mch_id'];
        $sign_data['nonce_str']  = $order['nonce_str'];
        $sign_data['package']   = 'Sign=WXPay';
        $sign_data['prepay_id'] = $order['prepay_id'];
        $sign_data['time_stamp'] = $time;

        $sign_string = 'appid='.$sign_data['appid'].'&noncestr='.$sign_data['nonce_str'].'&package='.$sign_data['package'].'&partnerid='.$order['mch_id'].'&prepayid='.$sign_data['prepay_id'].'&timestamp='.$sign_data['time_stamp'].'&key='.\WxPayConfig::KEY;
        $result_data['sign'] = strtoupper(md5($sign_string));

        $result_data['appid']      = $order['appid'];
        $result_data['nonce_str']  = $order['nonce_str'];
        $result_data['package']    = 'Sign=WXPay';
        $result_data['time_stamp'] = $time;
        $result_data['prepay_id']  = $order['prepay_id'];
        $result_data['mch_id']     = $order['mch_id'];

        apiResponse('1','请求成功',$result_data);
    }


    /**
     * 微信回调
     */
    public function wXinNotify(){
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $xml_res = $this->xmlToArray($xml);
        if($xml_res['result_code'] == 'SUCCESS'){
            $where['order_sn'] =$_POST["out_trade_no"];
            $where['status'] = array('eq',0);
            $recharge_info = M('MemberRecharge') ->where($where) ->find();
            if($recharge_info){
                //修改充值订单状态
                unset($where);
                $where['id'] = $recharge_info['id'];
                $data['update_time'] = time();
                $data['pay_status']  = 1;
                $data['pay_type']    = 2;
                $data['status']      = 1;
                $data['transaction_id'] = $xml_res['transaction_id'];
                $res = M('MemberRecharge')->where($where)->data($data)->save();
                if(!$res){
                    apiResponse('0','修改订单状态失败');
                }

                //添加用户余额
                $result = M('Member')->where(array('id'=>$recharge_info['m_id']))->setInc('balance',$recharge_info['price']);
                if(!$result){
                    apiResponse('0','添加用户余额失败');
                }

                //添加账单明细
                $result_data = D('Member','Logic') ->addDetail(1, $recharge_info['m_id'], 1, '充值', $recharge_info['price'], 1);
                if(!$result_data){
                    apiResponse('0','添加账单明细失败');
                }
            }else{
                apiResponse('0','充值失败');
            }
        }else{
            apiResponse('0','微信余额不足');
        }

        apiResponse('1','充值成功');
    }

    /**
     * 查询订单状态
     * 需要传递的参数：
     * 订单编号：order_sn
     */
    public function findStatus(){
        $where['order_sn'] =$_POST['order_sn'];
        $recharge_info =M('MemberRecharge')->where($where)->find();
        if($recharge_info['status'] == 0){
            $result_data['status'] = '0';
        }else{
            $result_data['status'] = '1';
        }
        apiResponse('1','',$result_data);
    }

    /**
     * 	作用：将xml转为array
     */
    function xmlToArray($xml){
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

    /*******************一道华丽的分割线******************/

    /**
     * 我的关注
     */
    public function mySign(){
        D('Member','Logic') ->mySign(I('post.'));
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
        if(empty($_POST['nickname'])){
            apiResponse('0','三方昵称不能为空');
        }
        if($_POST['open_type'] != 1&&$_POST['open_type'] != 2&&$_POST['open_type'] != 3){
            apiResponse('0','三方类型不能为空');
        }
        D('Member','Logic') ->bindThirdAccount(I('post.'));
    }

    /**
     * 用户积分列表
     * 用户token    token
     * 分页参数     p
     */
    public function integralList(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        D('Member','Logic') ->integralList(I('post.'));
    }
}
