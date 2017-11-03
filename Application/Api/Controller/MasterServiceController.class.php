<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class IndexController
 * @package Api\Controller
 * 首页模块
 */
class MasterServiceController extends BaseController{
    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 大师列表
     */
    public function masterList(){
        if(!$_POST['p']){
            apiResponse('0','分页参数不能为空');
        }
//        if(!$_POST['lat']){
//            apiResponse('0','获取纬度不能为空');
//        }
//        if(!$_POST['lng']){
//            apiResponse('0','获取经度不能为空');
//        }
        D('MasterService','Logic') ->masterList(I('post.'));
    }

    /**
     * 大师详情
     * 大师ID      master_id
     * 用户token   token
     */
    public function masterInfo(){
        if($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if(!$_POST['master_id']){
            apiResponse('0','大师ID不能为空');
        }
        D('MasterService','Logic') ->masterInfo(I('post.'));
    }

    /**
     * 评价列表
     * 大师ID   master_id
     * 分页参数 p
     */
    public function commentList(){
        if(!$_POST['master_id']){
            apiResponse('0','请输入大师对应的ID');
        }
        if(!$_POST['p']){
            apiResponse('0','分页参数不能为空');
        }
        D('MasterService','Logic') ->commentList(I('post.'));
    }

    /**
     * 商品列表
     * 大师ID   master_id
     * 分页参数 p
     */
    public function goodsList(){
        if(!$_POST['master_id']){
            apiResponse('0','请输入大师对应的ID');
        }
        if(!$_POST['p']){
            apiResponse('0','分页参数不能为空');
        }
        D('MasterService','Logic') ->goodsList(I('post.'));
    }

    /**
     * 新闻列表
     * 大师ID   master_id
     * 分页参数 p
     */
    public function newsList(){
        if(!$_POST['master_id']){
            apiResponse('0','请输入大师对应的ID');
        }
        if(!$_POST['p']){
            apiResponse('0','分页参数不能为空');
        }
        D('MasterService','Logic') ->newsList(I('post.'));
    }

    /**
     * 回答列表
     * 大师ID   master_id
     * 分页参数 p
     */
    public function answerList(){
        if(!$_POST['master_id']){
            apiResponse('0','请输入大师对应的ID');
        }
        if(!$_POST['p']){
            apiResponse('0','分页参数不能为空');
        }
        D('MasterService','Logic') ->answerList(I('post.'));
    }

    /**
     * 协会详情
     */
    public function socialInfo(){
        if(!$_POST['social_id']){
            apiResponse('0','协会ID不能为空');
        }
        D('MasterService','Logic') ->socialInfo(I('post.'));
    }

    /**
     * 协会成员
     */
    public function socialMaster(){
        if(!$_POST['social_id']){
            apiResponse('0','协会ID不能为空');
        }
        if(!$_POST['p']){
            apiResponse('0','分页参数不能为空');
        }
        D('MasterService','Logic') ->socialMaster(I('post.'));
    }

    /**
     * 协会列表
     */
    public function socialList(){
        if(!$_POST['p']){
            apiResponse('0','分页参数不能为空');
        }
        D('MasterService','Logic') ->socialList(I('post.'));
    }

    /**
     * 协会图库
     * 协会ID      p
     * 分页参数    social_id
     */
    public function socialPicture(){
        if(!$_POST['p']){
            apiResponse('0','分页参数不能为空');
        }
        if(!$_POST['social_id']){
            apiResponse('0','协会ID不能为空');
        }
        D('MasterService','Logic') ->socialPicture(I('post.'));
    }

    /**
     * 服务订单
     * 用户标识  token
     * 服务类型  m_s_id
     * 姓名      name
     * 性别      sex   1男  2女
     * 生日      birthday     格式2017-08-14-11-30
     * 出生地    city_id
     * 详情      content
     * 图片上传  ser_pic
     */
    public function serviceOrder(){
        if(!$_POST['token']&&!$_SERVER['HTTP_TOKEN']){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(!$_POST['m_s_id']){
            apiResponse('0','请选择服务类型');
        }
        if(!$_POST['name']){
            apiResponse('0','请输入您的姓名');
        }
        if(!$_POST['sex']){
            apiResponse('0','请选择您的性别');
        }
        if(!$_POST['birthday']){
            apiResponse('0','请选择您的生日');
        }
        if(!$_POST['city_id']){
            apiResponse('0','请选择您的出生地');
        }
        if(!$_POST['content']){
            apiResponse('0','请填写详情');
        }
        if(!$_POST['price']){
            apiResponse('0','服务价格不能为空');
        }
        D('MasterService','Logic') ->serviceOrder(I('post.'));
    }

    /**
     * 服务订单页面
     * 用户token   token
     * 订单编号    order_sn
     * 订单ID      sorder_id
     * 订单价格    price
     */
    public function serviceOrderPage(){
        if(!$_POST['token']&&!$_SERVER['HTTP_TOKEN']){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(!$_POST['order_sn']){
            apiResponse('0','订单编号不能为空');
        }
        if(!$_POST['sorder_id']){
            apiResponse('0','订单id不能为空');
        }
        if(!$_POST['price']){
            apiResponse('0','订单价格不能为空');
        }
        D('MasterService','Logic') ->serviceOrderPage(I('post.'));
    }

    /**
     * 服务订单支付前接口
     * 服务订单    sorder_id
     * 订单价格    price
     * 优惠券ID    coupon_id
     */
    public function payBefore(){
        if(!$_POST['sorder_id']){
            apiResponse('0','服务订单ID不能为空');
        }
        if(!$_POST['price']){
            apiResponse('0','服务订单价格不能为空');
        }
        D('MasterService','Logic') ->payBefore(I('post.'));
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