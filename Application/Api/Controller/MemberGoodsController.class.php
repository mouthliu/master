<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class IndexController
 * @package Api\Controller
 * 用户端商品模块
 */
class MemberGoodsController extends BaseController{
    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 首页列表
     */
    public function GoodsList(){
        D('MemberGoods','Logic') ->GoodsList(I('post.'));
    }

    /**
     * 首页详情
     * 商品ID    goods_id
     * 用户token token  可以为空
     */
    public function goodsInfo(){
        if(!$_POST['goods_id']){
            apiResponse('0','商品ID不能为空');
        }
        if($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('MemberGoods','Logic') ->goodsInfo(I('post.'));
    }

    /**
     * 大师的宝阁
     * 大师ID      master_id
     * 分页参数    p
     */
    public function masterGoods(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(!$_POST['master_id']){
            apiResponse('0','大师ID不能为空');
        }
        if(!$_POST['p']){
            apiResponse('0','分页参数不能为空');
        }
        D('MemberGoods','Logic') ->masterGoods(I('post.'));
    }

    /**
     * 商品评价
     * 商品ID       goods_id
     * 分页参数     p
     */
    public function goodsEvaluate(){
        if(!$_POST['goods_id']){
            apiResponse('0','商品ID不能为空');
        }
        if(!$_POST['p']){
            apiResponse('0','分页参数不能为空');
        }
        D('MemberGoods','Logic') ->goodsEvaluate(I('post.'));
    }

    /**
     * 加入购物车
     * 用户token     token
     * 商品ID        goods_id
     * 收藏数量      number
     */
    public function addShopCart(){
        //用户ID不能为空
        if(empty($_POST['token'])&&empty($_SERVER['HTTP_TOKEN'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //商品ID不能为空
        if(empty($_POST['goods_id'])){
            apiResponse('0','商品ID不能为空');
        }
        //购买数量不能为空
        if(empty($_POST['number'])){
            apiResponse('0','收藏数量不能为空');
        }
        D('MemberGoods','Logic') ->addShopCart(I('post.'));
    }

    /**
     * 修改购物车
     * 用户token    token
     * 购物车属性   cart_json   json串：[{"cart_id":"4","number":"5"},{"cart_id":"10","number":"6"}]
     */
    public function modifyShopCart(){
        //用户ID不能为空
        if(empty($_POST['token'])&&empty($_SERVER['HTTP_TOKEN'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //购物车属性值不能为空   并转化json串
        if(empty($_POST['cart_json'])){
            apiResponse('0','购物车属性不能为空');
        }
        D('MemberGoods','Logic') ->modifyShopCart(I('post.'));
    }

    /**
     * 删除购物车
     * 用户token   token
     * 购物车json  cart_json  [{"cart_id":"1"},{"cart_id":"2"}]
     */
    public function deleteShopCart(){
        //用户ID不能为空
        if(empty($_POST['token'])&&empty($_SERVER['HTTP_TOKEN'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //购物车json串
        if (empty($_POST['cart_json'])) {
            apiResponse('0', '请选择要删除的内容');
        }
        D('MemberGoods','Logic') ->deleteShopCart(I('post.'));
    }

    /**
     * 购物车列表
     * 用户token   token
     */
    public function shopCartList(){
        //用户ID不能为空
        if(empty($_POST['token'])&&empty($_SERVER['HTTP_TOKEN'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('MemberGoods','Logic') ->shopCartList(I('post.'));
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