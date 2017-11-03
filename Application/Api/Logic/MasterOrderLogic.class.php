<?php
namespace Api\Logic;
/**
 * 大师订单模块
 */
class MasterOrderLogic extends BaseLogic{

    /**
     * 商品订单列表
     */
    public function orderList($request = array()){
        $master = $this ->searchMaster($request['token']);

        //根据用户ID  查询订单
        $where['`order`.master_id'] = $master['id'];
        if($request['type'] != 5){
            $where['`order`.order_status'] = $request['type'];
        }elseif(empty($request['type'])){
            $where['`order`.order_status'] = 0;
        }
        $where['`order`.status'] = array('neq',10);
//        $field = '`order`.id as order_id, master.nickname, `order`.order_sn, `order`.order_serialization, `order`.real_price as total_price, `order`.pay_price, `order`.order_status';
        $field = '`order`.id as order_id, master.nickname, `order`.order_sn, `order`.order_serialization, `order`.total_price, `order`.pay_price, `order`.order_status';

        $order_info = '`order`.create_time desc';
        $order = D('Order') ->selectOrder($where, $field, $order_info, $request['p']);

        if(empty($order)){
            apiResponse('1','',array());
        }

        //查询商家信息以及订单信息
        foreach($order as $k =>$v) {
            $goods_list = unserialize($v['order_serialization']);
            $goods = array();
            $goods_num = 0;
            $freight = 0;
            foreach ($goods_list['goods'] as $key => $value){
                $goods[$key]['goods_id']      = $value['goodsDetail']['id'];
                $goods[$key]['goods_pic']     = $value['goodsDetail']['goods_pic'] ? C('API_URL') . $value['goodsDetail']['goods_pic'] : '';
                $goods[$key]['goods_name']    = $value['goodsDetail']['goods_name'] ? $value['goodsDetail']['goods_name'] : '';
                $goods[$key]['price']         = $value['goodsDetail']['real_price'] ? $value['goodsDetail']['real_price'] : '0.00';
//                $goods[$key]['price']         = $value['goodsDetail']['price'] ? $value['goodsDetail']['price'] : '0.00';
                $goods[$key]['goods_type']    = $value['goodsDetail']['goods_type'] ? $value['goodsDetail']['goods_type'] : '';
                $goods[$key]['num']           = $value['num'] ? $value['num'] : '0';
                $goods[$key]['freight']       = $value['freight']?$value['freight']:'0.00';
                $goods[$key]['total_price']   = $value['real_price'] ? $value['real_price'] : '0.00';
//                $goods[$key]['total_price']   = $value['price'] ? $value['price'] : '0.00';
                $goods_num                    = $goods_num + $goods[$key]['num'];
                $freight = $freight + $goods[$key]['freight'];
            }
            $goods = array_values($goods);
            $order[$k]['goods_list'] = $goods;
            $order[$k]['goods_num']  = ''.$goods_num;
            $order[$k]['freight']    = $freight?$freight.'':'0.00';
            unset($order[$k]['order_serialization']);
        }

        apiResponse('1','',$order);
    }

    /**
     * 商品订单详情
     */
    public function orderInfo($request = array()){
        $master = $this ->searchMaster($request['token']);
        //根据已有条件查询订单详情
        $where['`order`.id'] = $request['order_id'];
        $where['`order`.master_id'] = $master['id'];
        $where['`order`.status'] = array('neq',10);
//        $field = '`order`.id as order_id, `order`.order_sn, master.nickname, `order`.delivery, `order`.delivery_sn, `order`.address, `order`.order_serialization, `order`.pay_time, `order`.deliver_time, `order`.deal_time, `order`.coupon, `order`.real_price as total_price, `order`.pay_price, `order`.remark, `order`.freight, `order`.order_status, `order`.remark, `order`.create_time';
        $field = '`order`.id as order_id, `order`.order_sn, master.nickname, `order`.delivery, `order`.delivery_sn, `order`.address, `order`.order_serialization, `order`.pay_time, `order`.deliver_time, `order`.deal_time, `order`.coupon, `order`.total_price, `order`.pay_price, `order`.remark, `order`.freight, `order`.order_status, `order`.remark, `order`.create_time';
        $order_info = D('Order') ->selectOrder($where , $field , '', '', '', 1);

        if(empty($order_info)){
            apiResponse('0','订单详情有误');
        }

        $address    = unserialize($order_info['address']);
        $goods_info = unserialize($order_info['order_serialization']);
        if(!$goods_info){
            apiResponse('0','订单详情有误');
        }

        $i = 0;
        $freight = 0;
        $price = 0;
        foreach ($goods_info['goods'] as $k => $v) {
            $goods[$i]['goods_id']         = $v['goodsDetail']['id'];
            $goods[$i]['goods_pic']  = $v['goodsDetail']['goods_pic'] ? C('API_URL').$v['goodsDetail']['goods_pic'] : '';
            $goods[$i]['goods_name'] = $v['goodsDetail']['goods_name'] ? $v['goodsDetail']['goods_name'] : '';
            $goods[$i]['price']      = $v['goodsDetail']['real_price'] ? $v['goodsDetail']['real_price'] : '0.00';
//            $goods[$i]['price']      = $v['goodsDetail']['price'] ? $v['goodsDetail']['price'] : '0.00';
            $goods[$i]['goods_type'] = $v['goodsDetail']['goods_type'] ? $v['goodsDetail']['goods_type'] : '';
            $goods[$i]['num']        = $v['num'] ? $v['num'] : '0';
            $goods[$i]['freight']    = $v['freight'] ? $v['freight'] : '0';
            $goods[$i]['total_price']= $v['real_price'] ? $v['real_price'].'' : '0.00';
//            $goods[$i]['total_price']= $v['price'] ? $v['price'].'' : '0.00';
            $freight = $freight + $goods[$i]['freight'];
            $price += $goods[$i]['price']   ;
            $i = $i + 1;
        }

        //获取快递信息
        if($order_info['delivery'] != '0'){
            $delivery_company = M('DeliveryCompany') ->where(array('id'=>$order_info['delivery']))->field('id as delivery_id, delivery_code, company_name') ->find();
//            $order['delivery_company'] = $delivery_company;
//            $result['key'] = '6c8d76a022fca426';
        }else{
//            $order['delivery_company'] = array();
//            $result['key'] = '';
            $delivery_company = '';
        }

        $result['delivery_sn'] = $order_info['delivery_sn']?$order_info['delivery_sn']:'';
        $result['service_account'] = '8001';
        $result['service_pic'] = C('API_URL').'/Uploads/Member/service.png';
        //开始传入数据
        $order['goods_list']      = $goods;
        $order['address']         = $address;
        $order['order_status']    = $order_info['order_status'];
        $order['total_price']     = $order_info['total_price'];
        $order['freight']         = $freight?$freight.'':'0.00';
        $order['order_sn']        = $order_info['order_sn'];
        $order['order_id']        = $order_info['order_id'];
        $order['remark']          = $order_info['remark'];
        $order['create_time']     = date('Y-m-d H:i',$order_info['create_time']);
        $order['pay_time']        = $order_info['pay_time'] != 0?date('Y-m-d H:i',$order_info['pay_time']):'';
        $order['deliver_time']    = $order_info['deliver_time'] != 0?date('Y-m-d H:i',$order_info['deliver_time']):'';
        $order['deal_time']       = $order_info['deal_time'] != 0?date('Y-m-d H:i',$order_info['deal_time']):'';
        $order['nickname']        = $order_info['nickname'];
        $order['price']           = $price?$price.'':'0.00';
        $order['pay_price']       = $order_info['pay_price']?$order_info['pay_price'].'':'0.00';
        $order['delivery_sn']     = $order_info['delivery_sn']?$order_info['delivery_sn']:'';
        $order['company_name']    = $delivery_company['company_name']?$delivery_company['company_name']:'';

        if(empty($order)){
            apiResponse('0','订单详情有误');
        }

        apiResponse('1','',$order);
    }

    /**
     * 商家发货
     */
    public function deliverGoods($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where  = array('master_id'=>$master['id'],'id'=>$request['order_id'],'status'=>array('neq',10));
        $order  = $this ->easyMysql('Order','3',$where);
        if(!$order){
            apiResponse('0','订单信息有误');
        }
        $data['order_status'] = 2;
        $data['delivery']     = $request['delivery_id'];
        $data['delivery_sn']  = $request['delivery_sn'];
        $data['deliver_time'] = time();
        $data['update_time']  = time();
        $order_res  = $this ->easyMysql('Order','2',$where,$data);
        if(!$order_res){
            apiResponse('0','发货失败');
        }
        unset($where);
        unset($data);
        $date['user_id'] = $master['id'];
        $date['user_type'] = 2;
        $date['type'] = 3;
        $date['object_id'] = $order['id'];
        $date['headline']  = '宝阁消息';
        $date['content']   = '您订单号为'.$order['order_sn'].'的订单已成功发货。';
        $date['create_time'] = time();
        $message = M('Message') ->add($date);

        //极光推送
        //调用极光推送
        $con   = $date['content'];
//        $arr_j = $date['user_id'];
        $arr_j = $master['token'];
        $this->jPushToMaster($arr_j, $order['id'], $con ,$con);

        $data['user_id']     = $order['m_id'];
        $data['user_type']   = 1;
        $data['type']        = 3;
        $data['headline']    = '宝阁消息';
        $date['object_id']   = $order['id'];
        $data['content']     = '您订单号为'.$order['order_sn'].'的订单已成功发货。';
        $data['create_time'] = time();
        $message = M('Message') ->add($data);

        $member = $this ->easyMysql('Member',3,array('id'=>$order['m_id']));
        if($member['push_message'] == 1){
            //极光推送
            //调用极光推送
            $con   = $data['content'];
//        $arr_j = $data['user_id'];
            $arr_j = $member['token'];
            $this->jPushToMember($arr_j, $order['id'], $con ,$con);
        }

        apiResponse('1','发货成功');
    }

    /**
     * 删除订单
     */
    public function deleteOrder ($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where  = array('id'=>$request['order_id'],'master_id'=>$master['id'],'status'=>array('neq',10));
        $order  = $this ->easyMysql('Order','3',$where);
        if(!$order){
            apiResponse('0','订单信息有误');
        }
        $data['status'] = 10;
        $data['update_time'] = time();
        $res = $this ->easyMysql('Order','2',$where,$data);
        if(!$res){
            apiResponse('0','删除订单失败');
        }

        apiResponse('1','删除订单成功');
    }

    /**
     * 协商退货界面
     */
    public function refundOrderPage($request = array()){
        $master = $this ->searchMaster($request['token']);
        $which  = array('order_id'=>$request['order_id'], 'order_type'=>2, 'status'=>array('neq',9));
        $customer = $this ->easyMysql('Customer','3',$which);

        $where['order_id'] = $request['order_id'];
        $where['order_type'] = 2;
        $field = 'id as refund_id, user_type, headline, content, picture, create_time, status, price';
        $order = 'create_time asc, id asc';
        $refund = $this ->easyMysql('MessageRefund',4,$where,'',$field,$order);
        if(!$refund){
            $refund = array();
        }else{
            foreach($refund as $k => $v){
                $refund[$k]['create_time'] = date('m-d H:i',$v['create_time']);
                if($v['picture'] != ''){
                    $picture = array();
                    $pic = explode(',',$v['picture']);
                    foreach($pic as $key => $val){
                        unset($photo);
                        $photo = $this ->searchPhoto($val);
                        $picture[$k]['pic'] = $photo?$photo:'';
                    }
                }
                $refund[$k]['picture'] = $picture?$picture:array();
                $refund[$k]['customer_type'] = $customer['customer_type'];
            }
        }

        apiResponse('1','',$refund);
    }

    /**
     * 取消退货同意退货
     */
    public function typeApply($request = array()){
        $master = $this ->searchMaster($request['token']);

        $where = array('id'=>$request['order_id'],'status'=>array('neq',9),'order_status'=>6);
        $order = $this ->easyMysql('Order',3,$where);
        if(!$order){
            apiResponse('0','订单信息有误');
        }
        if($request['type'] == 2){
            $data['order_status'] = 8;
            $data['update_time']  = time();
            $order_res = $this ->easyMysql('Order',2,$where,$data);
        }
        unset($where);
        unset($data);
        $where['order_type'] = 2;
        $where['order_id']   = $request['order_id'];
        $where['customer_status']     = 0;
        $customer = $this ->easyMysql('Customer',3,$where);
        if(!$customer){
            apiResponse('0','协议信息有误');
        }
        if($request['type'] == 1){
            $data['customer_status'] = 1;
        }else{
            $data['customer_status'] = 5;
        }
        $data['update_time'] = time();
        $res = $this ->easyMysql('Customer',2,$where,$data);
        if(!$res){
            apiResponse('0','操作有误');
        }

        unset($data);
        $data['user_type'] = 2;
        $data['user_id']  = $master['id'];
        $data['order_id'] = $request['order_id'];
        $data['order_type'] = 2;
        if($request['type'] == 1){
            $data['headline'] = '';
            $data['content'] = '卖家同意退货申请';
        }else{
            $data['headline'] = '卖家拒绝申请';
            $data['content'] = '卖家拒绝该订单的退款申请';
        }
        $data['create_time'] = time();
        if($request['type'] == 1){
            $data['status'] = 1;
        }else{
            $data['status'] = 5;
        }
        $message = $this ->easyMysql('MessageRefund',1,'',$data);

        //输入用户信息  商家信息  商家详情  商家明细
        if($request['type'] == 1){
            $data_member = '您订单号为'.$order['order_sn'].'的订单大师已同意退货。';
            $data_master = '您订单号为'.$order['order_sn'].'的订单已同意退货，请填写您的收货地址。';
        }else{
            $data_member = '您订单号为'.$order['order_sn'].'的订单大师已拒绝了您的退货申请。';
            $data_master = '您订单号为'.$order['order_sn'].'的订单已拒绝该用户的退款申请。';
        }
        $message_member = $this ->addMessage(1,$order['m_id'],3,$request['order_id'],'宝阁消息',$data_member);

        $member = $this ->easyMysql('Member',3,array('id'=>$order['m_id']));
        if($member['push_message'] == 1){
            //极光推送
            //调用极光推送
            $con = $data_member;
            $arr_j = $member['token'];
            $this->jPushToMember($arr_j, $request['order_id'], $con ,$con);
        }

        $message_master = $this ->addMessage(2,$order['master_id'],3,$request['order_id'],'宝阁消息',$data_master);

        //极光推送
        //调用极光推送
        $con = $data_master;
        $arr_j = $master['token'];
        $this->jPushToMaster($arr_j, $request['order_id'], $con ,$con);

        apiResponse('1','操作成功');
    }

    /**
     * 填写退货地址
     */
    public function returnAddress($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where['id'] = $request['order_id'];
        $where['status'] = array('neq',9);
        $where['order_status'] = 6;
        $order = $this ->easyMysql('Order',3,$where);
        if(!$order){
            apiResponse('0','订单信息有误');
        }

        unset($where);
        $where['order_type'] = 2;
        $where['order_id']   = $request['order_id'];
        $where['customer_status']     = 1;
        $customer = $this ->easyMysql('Customer',3,$where);
        if(!$customer){
            apiResponse('0','协议信息有误');
        }
        $data['people_name'] = $request['people_name'];
        $data['telephone']   = $request['telephone'];
        $data['address']     = $request['address'];
        $data['update_time'] = time();
        $data['customer_status'] = 2;
        $res = $this ->easyMysql('Customer',2,$where,$data);
        if(!$res){
            apiResponse('0','填写地址失败');
        }
        unset($data);
        $data['user_type'] = 2;
        $data['user_id']  = $master['id'];
        $data['order_id'] = $request['order_id'];
        $data['order_type'] = 2;
        $data['headline'] = '卖家退货地址';
        $data['content'] = '卖家联系人：'.$request['people_name'].'；卖家联系电话'.$request['telephone'].'；卖家退货地址'.$request['address'].'。';
        $data['create_time'] = time();
        $data['status']   = 2;
        $message = $this ->easyMysql('MessageRefund',1,'',$data);
        if(!$message){
            apiResponse('0','写入数据有误');
        }

        $message_member = $this ->addMessage(1,$order['m_id'],3,$request['order_id'],'宝阁消息','您订单号为'.$order['order_sn'].'的订单大师已填写退货地址，请您确认之后立即退货。');
        $member = $this ->easyMysql('Member',3,array('id'=>$order['m_id']));
        if($member['push_message'] == 1){
            //极光推送
            //调用极光推送
            $con = '您订单号为'.$order['order_sn'].'的订单大师已填写退货地址，请您确认之后立即退货。';
            $arr_j = $member['token'];
            $this->jPushToMember($arr_j, $request['order_id'], $con ,$con);
        }

        $message_master = $this ->addMessage(2,$order['master_id'],3,$request['order_id'],'宝阁消息','您订单号为'.$order['order_sn'].'的订单已填写退货地址，请等待用户退货。');

        //极光推送
        //调用极光推送
        $con = '您订单号为'.$order['order_sn'].'的订单已填写退货地址，请等待用户退货。';
        $arr_j = $master['token'];
        $this->jPushToMaster($arr_j, $request['order_id'], $con ,$con);

        apiResponse('1','填写地址成功');
    }

    /**
     * 卖家确认收货
     */
    public function confirmGoods($request = array()){
        $master = $this ->searchMaster($request['token']);

        $where = array('id'=>$request['order_id'],'status'=>array('neq',9));
        $order = $this ->easyMysql('Order',3,$where);
        if(!$order){
            apiResponse('0','订单信息有误');
        }
        $data['order_status'] = 7;
        $data['update_time']  = time();
        $order_res = $this ->easyMysql('Order',2,$where,$data);

        unset($where);
        unset($data);
        $where['order_type'] = 2;
        $where['order_id']   = $request['order_id'];
        $customer = $this ->easyMysql('Customer',3,$where);
        if(!$customer){
            apiResponse('0','协议信息有误');
        }

        $data['status'] = 4;
        $data['create_time'] = time();
        $res = $this ->easyMysql('Customer',2,$where,$data);
        if(!$res){
            apiResponse('0','确认收货失败');
        }

//        $mater = $this ->setType('Master',array('id'=>$master['id']),'balance',$customer['price'],2);
        $member_price = $this ->setType('Member',array('id'=>$customer['m_id']),'balance',$customer['price'],1);

        $this ->addDetail(1, $customer['m_id'], 1, '商品退款', 1, $customer['price']);

        unset($data);
        $data['user_type'] = 2;
        $data['user_id']  = $master['id'];
        $data['order_id'] = $request['order_id'];
        $data['order_type'] = 2;
        $data['headline'] = '卖家确认收货';
        $data['content'] = '卖家确认收货';
        $data['create_time'] = time();
        $data['status'] = 4;
        $message = $this ->easyMysql('MessageRefund',1,'',$data);

        unset($data);
        $data['user_type'] = 2;
        $data['user_id']  = $master['id'];
        $data['order_id'] = $request['order_id'];
        $data['order_type'] = 2;
        $data['headline'] = '';
        $data['content'] = '卖家给买家打款'.$customer['price'].'元。';
        $data['create_time'] = time();
        $data['status'] = 4;
        $message = $this ->easyMysql('MessageRefund',1,'',$data);

        unset($data);
        $data['user_type'] = 2;
        $data['user_id']  = $master['id'];
        $data['order_id'] = $request['order_id'];
        $data['order_type'] = 2;
        $data['headline'] = '';
        $data['content'] = '退款成功';
        $data['create_time'] = time();
        $data['status']  = 4;
        $message = $this ->easyMysql('MessageRefund',1,'',$data);

        $message_member = $this ->addMessage(1,$order['m_id'],3,$request['order_id'],'宝阁消息','您订单号为'.$order['order_sn'].'的订单大师已确认收货，请您对账单明细进行相应审核。');
        $member = $this ->easyMysql('Member',3,array('id'=>$order['m_id']));
        if($member['push_message'] == 1){
            //极光推送
            //调用极光推送
            $con = '您订单号为'.$order['order_sn'].'的订单大师已确认收货，请您对账单明细进行相应审核。';
            $arr_j = $member['token'];
            $this->jPushToMember($arr_j, $request['order_id'], $con ,$con);
        }

        $message_master = $this ->addMessage(2,$order['master_id'],3,$request['order_id'],'宝阁消息','您订单号为'.$order['order_sn'].'的订单已确认收货。');

        //极光推送
        //调用极光推送
        $con = '您订单号为'.$order['order_sn'].'的订单已确认收货。';
        $arr_j = $master['token'];
        $this->jPushToMaster($arr_j, $request['order_id'], $con ,$con);

        apiResponse('1','确认收货成功');
    }
}