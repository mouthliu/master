<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class IndexController
 * @package Api\Controller
 * 首页模块
 */
class IndexController extends BaseController{
    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }
    /**
     * 首页接口
     * 用户token   token   4e31b18e41db430722ef4559993322c9
     */
    public function indexPage(){
        if($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Index','Logic') ->indexPage(I('post.'));
    }

    /**
     * 各种算命
     * type  1  姓名测试  2  号码测试  3  情感自测  4  星座运势  5  解梦  6  前世今生
     */
    public function fortuneTelling(){
        if($_GET['type'] != 1&&$_GET['type'] != 2&&$_GET['type'] != 3&&$_GET['type'] != 4&&$_GET['type'] != 5&&$_GET['type'] != 6){
            apiResponse('0','状态有误');
        }
        if(!$_GET['keyword']){
            apiResponse('0','请填写测试内容');
        }
        D('Index','Logic') ->fortuneTelling(I('get.'));
    }

    /**
     * 签到
     * 用户token   token   4e31b18e41db430722ef4559993322c9
     */
    public function sign(){
        if(empty($_SERVER['HTTP_TOKEN'])&&empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif(!empty($_SERVER['HTTP_TOKEN'])){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Index','Logic') ->sign(I('post.'));
    }

    /**
     * 商品列表
     * 分页参数    p
     */
    public function goodsList(){
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        D('Index','Logic') ->goodsList(I('post.'));
    }

    /**
     * 商品类别列表
     */
    public function goodsTypeList(){
        D('Index','Logic') ->goodsTypeList(I('post.'));
    }

    /**
     * 日历表
     */
    public function calendar(){
        if(!empty($_SERVER['HTTP_TOKEN'])){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Index','Logic') ->calendar(I('post.'));
    }

    /**
     * 月历表
     */
    public function monthCalendar(){

        D('Index','Logic') ->monthCalendar(I('post.'));
    }

    /**
     * 今日运势
     */
    public function fortuneToday(){
        if(!empty($_SERVER['HTTP_TOKEN'])){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Index','Logic') ->fortuneToday(I('post.'));
    }

    /**
     * 本周运势
     */
    public function fortuneWeek(){
        if(!empty($_SERVER['HTTP_TOKEN'])){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Index','Logic') ->fortuneWeek(I('post.'));
    }

    /*********************一道华丽的分割线************************/

    /**
     * 支付宝回调
     * 订单号    out_trade_no
     * 支付宝状态  trade_status  TRADE_SUCCESS
     */
    public function alipayNotify(){
        $out_trade_no = $_POST['out_trade_no'];
        $trade_status = $_POST['trade_status'];
        if($trade_status == 'TRADE_SUCCESS'){
            $where['order_sn'] =$out_trade_no;
            $where['status'] = array('eq',0);
            $order = M('AddOrder')->where($where)->find();
            if($order){
                $r_order = M('ReleaseOrder') ->where(array('id'=>$order['r_order_id'])) ->find();

                //修改充值订单状态
                unset($where);
                $where['id'] = $order['id'];
                $data['update_time'] = time();
                $data['pay_status']  = 1;
                $data['pay_type']    = 2;
                $data['status']      = 2;
                M('AddOrder') ->where($where) ->data($data) ->save();

                //添加账单明细  帮带订单  addorder付款  求带订单  releaseOrder付款
                unset($data);

                if($order['type'] == 1){
                    $data['title']       = '帮带订单';
                    $data['m_id']        = $order['m_id'];
                    $data_two['m_id']    = $r_order['m_id'];
                    $nickname = M('Member') ->where(array('id'=>$r_order['m_id'])) ->getField('nickname');
                    $nickname_res = M('Member') ->where(array('id'=>$order['m_id'])) ->getField('nickname');
                }else{
                    $data['title']       = '求带订单';
                    $data['m_id'] = $r_order['m_id'];
                    $data_two['m_id'] = $order['m_id'];
                    $nickname = M('Member') ->where(array('id'=>$order['m_id'])) ->getField('nickname');
                    $nickname_res = M('Member') ->where(array('id'=>$r_order['m_id'])) ->getField('nickname');
                }
                $data['symbol']      = 2;
                $data['price']       = $order['pay_price'];
                $data['create_time'] = time();
                $data['date'] = date('Y-m',time());
                M('Detail')->data($data)->add();

                //写入一条订单信息
                $dat['a_order_id'] = $order['id'];
//                if($order['type'] == 1){
//                    $dat['order_type'] = 2;
//                }else{
//                    $dat['order_type'] = 1;
//                }
                $dat['order_type'] = 2;
                $dat['m_id']       = $data['m_id'];
                $dat['type']       = 2;
                $dat['headline']   = '帮带人：'.$nickname;
                if($order['type'] == 1){
                    $dat['content'] = '您已付款，请等待交接';
                }else{
                    $dat['content'] = '您已付款，请等待交接';
                }

                $dat['order_number'] = $order['order_sn'];
                $dat['create_time'] = time();
                $message = M('Message') ->data($dat) ->add();

                $dynamic_delete = deleteDynamic($order['id'],$data['m_id'],$message);

                //写入动态表
                unset($dat);
                $dat['type'] = 3;
                $dat['user_id'] = $data['m_id'];
                $dat['m_id']    = $data_two['m_id'];
                $dat['create_time'] = time();
                $dat['object_id'] = $message;
                $dynamic = M('Dynamic') ->add($dat);

                unset($dat);
                //帮带人写入一条订单信息
                $dat['a_order_id'] = $order['id'];
                $dat['order_type'] = $order['type'];
                $dat['order_type'] = 1;
                $dat['type']       = 2;
                $dat['headline']   = '求带人：'.$nickname_res;
                if($order['type'] == 1){
                    $dat['m_id'] = $r_order['m_id'];
                    $dat['content'] = '求带人已付款，等待交接';
                }else{
                    $dat['m_id'] = $order['m_id'];
                    $dat['content'] = '求带人已付款，等待交接';
                }

                $dat['order_number'] = $order['order_sn'];
                $dat['create_time'] = time();
                $message = M('Message') ->data($dat) ->add();

                $dynamic_delete = deleteDynamic($order['id'],$dat['m_id'],$message);

                //帮带人写入动态表
                unset($dat);
                $dat['type'] = 3;
                if($order['type'] == 1){
                    $dat['user_id'] = $r_order['m_id'];
                    $dat['m_id'] = $order['m_id'];
                }else{
                    $dat['user_id'] = $order['m_id'];
                    $dat['m_id'] = $r_order['m_id'];
                }
                $dat['create_time'] = time();
                $dat['object_id'] = $message;
                $dynamic = M('Dynamic') ->add($dat);

            }else{
                apiResponse('300','充值失败');
            }
        }else{
            apiResponse('300','支付宝余额不足');
        }

        apiResponse('200','支付成功');
    }

    /**
     * 获取微信支付参数
     * 传递参数的方式：post
     * 需要传递的参数：
     * 订单id：order_id
     */
    public function getWXPayParam(){
        Vendor('WxPayApp.lib.WxPay#Api');
        if(empty($_POST['a_order_id'])){
            apiResponse('100','参数不完整');
        }
        //查询订单信息
        $info = M('AddOrder')->where(array('id'=>$_POST['a_order_id']))->find();
        if(!$info){
            apiResponse('300','订单信息查询失败');
        }

        //②、统一下单
        $input = new \WxPayUnifiedOrder();
        $input->SetBody("微带支付");
        $input->SetAttach("微带支付");
        $input->SetOut_trade_no($info['order_sn']);
//        $input->SetTotal_fee($info['pay_price']*100);
        $input->SetTotal_fee(1);

        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("APP支付");
        $input->SetNotify_url("http://wday.txunda.com/index.php/Api/Index/wXinNotify");
        $input->SetTrade_type("APP");
        $order = \WxPayApi::unifiedOrder($input);
//apiResponse('error','',$order);
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

        apiResponse('200','请求成功',$result_data);
    }

    /**
     * 微信回调
     * 订单号   out_trade_no
     */
    public function wXinNotify(){
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $xml_res = $this->xmlToArray($xml);
        if($xml_res['result_code'] == 'SUCCESS'){

            $where['order_sn'] =$xml_res["out_trade_no"];
            $where['status'] = array('eq',0);
            $order = M('AddOrder') ->where($where) ->find();
            if($order){
                $r_order = M('ReleaseOrder') ->where(array('id'=>$order['r_order_id'])) ->find();

                //修改充值订单状态
                unset($where);
                $where['id'] = $order['id'];
                $data['update_time'] = time();
                $data['pay_status']  = 1;
                $data['pay_type']    = 3;
                $data['status']      = 2;
                $result = M('AddOrder')->where($where)->data($data)->save();
                if(!$result){
                    apiResponse('300','充值失败');
                }

                //添加账单明细
                unset($data);
                if($order['type'] == 1){
                    $data['title']       = '帮带订单';
                    $data['m_id']        = $order['m_id'];
                    $data_one['m_id']    = $r_order['m_id'];
                    $nickname = M('Member') ->where(array('id'=>$r_order['m_id'])) ->getField('nickname');
                    $nickname_res = M('Member') ->where(array('id'=>$order['m_id'])) ->getField('nickname');
                }else{
                    $data['title']       = '求带订单';
                    $data['m_id']        = $r_order['m_id'];
                    $data_one['m_id']    = $order['m_id'];
                    $nickname = M('Member') ->where(array('id'=>$order['m_id'])) ->getField('nickname');
                    $nickname_res = M('Member') ->where(array('id'=>$r_order['m_id'])) ->getField('nickname');
                }
                $data['symbol']      = 2;
                $data['price']       = $order['pay_price'];
                $data['create_time'] = time();
                $data['date'] = date('Y-m',time());
                M('Detail')->data($data)->add();

                //写入一条订单信息
                $dat['a_order_id'] = $order['id'];
                $dat['order_type'] = 2;
                $dat['m_id']       = $data['m_id'];
                $dat['type']       = 2;
                $dat['headline']   = '帮带人：'.$nickname;
                $dat['content'] = '您已付款，请等待交接';
                $dat['order_number'] = $order['order_sn'];
                $dat['create_time'] = time();
                $message = M('Message') ->data($dat) ->add();

                $dynamic_delete = deleteDynamic($order['id'],$dat['m_id'],$message);

                //写入动态表
                unset($dat);
                $dat['type'] = 3;
                $dat['user_id'] = $data['m_id'];
                $dat['m_id']    = $data_one['m_id'];
                $dat['create_time'] = time();
                $dat['object_id'] = $message;
                $dynamic = M('Dynamic') ->add($dat);

                unset($dat);
                //帮带人写入一条订单信息
                $dat['a_order_id'] = $order['id'];
//                $dat['order_type'] = $order['type'];
                $dat['order_type'] = 1;
                $dat['type']       = 2;
                $dat['headline']   = '求带人：'.$nickname_res;
                if($order['type'] == 1){
                    $dat['m_id'] = $r_order['m_id'];
                    $dat['content'] = '求带人已付款，等待交接';
                }else{
                    $dat['m_id'] = $order['m_id'];
                    $dat['content'] = '求带人已付款，等待交接';
                }

                $dat['order_number'] = $order['order_sn'];
                $dat['create_time'] = time();
                $message = M('Message') ->data($dat) ->add();

                $dynamic_delete = deleteDynamic($order['id'],$dat['m_id'],$message);

                //帮带人写入动态表
                unset($dat);
                $dat['type'] = 3;
                if($order['type'] == 1){
                    $dat['user_id'] = $r_order['m_id'];
                    $dat['m_id']    = $order['m_id'];
                }else{
                    $dat['user_id'] = $order['m_id'];
                    $dat['m_id']    = $r_order['m_id'];
                }
                $dat['create_time'] = time();
                $dat['object_id'] = $message;
                $dynamic = M('Dynamic') ->add($dat);
            }else{
                apiResponse('300','支付失败');
            }
        }else{
            apiResponse('300','微信余额不足');
        }

        apiResponse('200','充值成功');
    }

    /**
     * 查询订单状态
     * 需要传递的参数：
     * 订单编号：order_sn
     */
    public function findStatus(){
        $where['order_sn'] =$_POST['order_sn'];
        $order =M('AddOrder')->where($where)->find();
        if($order['status'] == 0){
            $result_data['status'] = '0';
        }else{
            $result_data['status'] = '1';
        }
        apiResponse('200','',$result_data);
    }

    /**
     * 	作用：将xml转为array
     */
    function xmlToArray($xml){
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }
}