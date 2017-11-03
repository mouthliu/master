<?php
namespace Api\Logic;
/**
 * 用户订单模块
 */
class MemberOrderLogic extends BaseLogic{

    /**
     * 单品提交订单页面
     */
    public function submitOrderPage($request = array()){
        $member = $this ->searchMember($request['token']);

        //查询是否存在默认地址   如果有直接返回
        if(!$request['address_id']){
            $where['m_id'] = $member['id'];
            $where['is_default'] = 1;
            $where['status'] = array('neq',9);
            $address = M('Address') ->where($where) ->field('id as address_id, name, telephone, province, city, area, address_info, is_default') ->find();
        }else{
            $where['id'] = $request['address_id'];
            $where['status'] = array('neq',9);
            $address = M('Address') ->where($where) ->field('id as address_id, name, telephone, province, city, area, address_info, is_default') ->find();
        }

        if(empty($address)){
            $address['status'] = '2';
        }else{
            $address['province'] = M('Region') ->where(array('id'=>$address['province'])) ->getField('region_name');
            $address['city']     = M('Region') ->where(array('id'=>$address['city'])) ->getField('region_name');
            $address['area']     = M('Region') ->where(array('id'=>$address['area'])) ->getField('region_name');
            $address['status'] = '1';
        }
        $result['address'] = $address;

        //根据商品ID查询商品信息
        unset($where);
        $where['id']         = $request['goods_id'];
        $where['is_shelves'] = 1;
        $where['status']     = 1;
        $goods_info = M('Goods') ->where($where) ->field('id as goods_id, master_id, goods_name, goods_pic, goods_type, price ,freight') ->find();
        $path = M('File') ->where(array('id'=>$goods_info['goods_pic']))->getField('path');
        $goods_info['goods_pic'] = $path?C("API_URL").$path:'';
        $goods_info['goods_type'] = $this ->easyMysql('GoodsType',5,array('id'=>$goods_info['goods_type']),'','type_name');
        unset($where);
        $where['id'] = $goods_info['master_id'];
        $where['status'] = array('neq',9);
        $goods_info['master_name'] = M('Master') ->where($where) ->getField('nickname');
        $goods_info['num'] = $request['num'].'';

        $goods_info['price'] = $this ->goodsPrice($goods_info['price'],1);

        $goods_info['total_price'] = ($goods_info['price'] * $request['num'] + $goods_info['freight']).'';
        $goods_info['balance'] = $member['balance'];
        $result['goods_info'] = $goods_info;
        apiResponse('1','',$result);
    }

    /**
     * 单品提交订单     需要消息提醒
     */
    public function submitOrder($request = array()){
        $member = $this ->searchMember($request['token']);

        //查询地址信息是否存在
        $where['id']     = $request['address_id'];
        $where['status'] = array('neq',9);
        $address_info    = M('Address')->where($where)->find();
        if(empty($address_info)){
            apiResponse('0','地址信息有误');
        }
        $address_info['province'] = M('Region') ->where(array('id'=>$address_info['province'])) ->getField('region_name');
        $address_info['city']     = M('Region') ->where(array('id'=>$address_info['city']))     ->getField('region_name');
        $address_info['area']     = M('Region') ->where(array('id'=>$address_info['area']))     ->getField('region_name');

        //查询商品是否存在
        $goods_info = M('Goods')->where(array('id'=>$request['goods_id'],'status'=>1))->find();
        if(empty($goods_info)){
            apiResponse('0','商品不存在');
        }

        //通过商品信息获取商家id ，查询商家是否存在
        unset($where);
        $where['id']     = $goods_info['master_id'];
        $where['status'] = array('eq',1);
        $merchant_info = M('Master')->where($where)->find();
        if(!$merchant_info){
            apiResponse('0','该大师信息有误');
        }

//        $goods_type = M('GoodsType') ->where(array('id'=>$goods_info['goods_type'])) ->getField('type_name');
        $goods_price = $this ->goodsPrice($goods_info['price'],1);
        //查询优惠券信息
        if($request['coupon_id']){
            $where['id'] = $request['coupon_id'];
            $where['status'] = 0;
            $coupon = $this ->easyMysql('MemberCoupon',3,$where);
            if(!$coupon){
                apiResponse('0','优惠券信息有误');
            }
        }

        //订单总表下单
        $order_total_sn = 'a'.date("Ymd",time()).rand(1000000,9999999);
        $data['m_id']              = $member['id'];
        $data['address_id']        = $request['address_id'];
        $data['order_total_sn']    = $order_total_sn;
        $data['order_total_price'] = $goods_price * $request['num'] + $goods_info['freight'];
        $data['create_time']       = time();
        $order_group_res = M('OrderGroup')->data($data)->add();
        if(empty($order_group_res)){
            apiResponse('0','下单失败');
        }

        //订单分表下单
        unset($data);
        $order_sn = date("Ymd",time()).rand(1000000,9999999);
        $data['m_id']        = $member['id'];
        $data['master_id']   = $goods_info['master_id'];
        $data['goods_id']    = $request['goods_id'];
        $data['order_total_id']  = $order_group_res;
        $data['address']     = serialize($address_info);
        $data['order_sn']    = $order_sn;
        $trade_price = $goods_price * $request['num'];
        $data['create_time'] = time();
        //购买数量  商品id  商品名称  商品单价  商品类别  商品图片  订单总价  订单运费
        $goods_info_serialization['goods'][0]['num'] = $request['num'];
        $goods_info_serialization['goods'][0]['goodsDetail']['id']         = $goods_info['id'];
        $goods_info_serialization['goods'][0]['goodsDetail']['goods_name'] = $goods_info['goods_name'];
        $goods_info_serialization['goods'][0]['goodsDetail']['price']      = $goods_price;
        $goods_info_serialization['goods'][0]['goodsDetail']['real_price'] = $goods_info['price'];
        $goods_pic = $goods_info['goods_pic']?$goods_info['goods_pic']:'';
//        $goods_info_serialization['goods'][0]['goodsDetail']['goods_type'] = $goods_type;
        $goods_info_serialization['goods'][0]['goodsDetail']['goods_pic'] = M('File')->where(array('id'=>$goods_pic))->getField('path');

        if($coupon){
            $goods_info_serialization['goods'][0]['price'] = $goods_price * $request['num'] + $goods_info['freight'] - $coupon['discount_price'];
            $goods_info_serialization['goods'][0]['real_price'] = $goods_info['price'] * $request['num'] + $goods_info['freight'] - $coupon['discount_price'];
            $data['real_price'] = $goods_info_serialization['goods'][0]['real_price'];
        }else{
            $goods_info_serialization['goods'][0]['price'] = $goods_price * $request['num'] + $goods_info['freight'];
            $goods_info_serialization['goods'][0]['real_price'] = $goods_info['price'] * $request['num'] + $goods_info['freight'];
            $data['real_price'] = $goods_info_serialization['goods'][0]['price'];
        }

        $goods_info_serialization['goods'][0]['freight'] = $goods_info['freight'];
        $data['order_serialization'] = serialize($goods_info_serialization);
        if($request['remark']){
            $data['remark'] = $request['remark'];
        }

        $data['total_price'] = $goods_price * $request['num'] + $goods_info['freight'];
        if($coupon){
            $data['pay_price'] = $data['total_price'] - $coupon['discount_price'];
        }else{
            $data['pay_price'] = $data['total_price'];
        }

        $data['freight']  = $goods_info['freight'];
        $data['remark']   = $request['remark']?$request['remark']:'';

        $add_order_res = M('Order')->data($data)->add();

        unset($where);
        $date['user_type'] = 1;
        $date['user_id']   = $member['id'];
        $date['type']      = 3;
        $date['object_id'] = $add_order_res;
        $date['headline']  = '宝物信息';
        $date['content']   = '您订单号为'.$order_sn.'的订单已成功下单，请立即支付。';
        $date['create_time'] = time();
        $message = M('Message') ->add($date);

        //极光推送
        //调用极光推送
        $con = $date['content'];
        $arr_j = $date['user_id'];
        $this->jPushToMember($arr_j,$add_order_res, $con ,$con);

        if($add_order_res){
            $result['order_id']  = $add_order_res;
            $result['order_sn']  = $order_total_sn;
            $result['price']     = $data['pay_price'].'';
            $result['goods_name'] = $goods_info['goods_name'];
            $result['satisty_price'] = $coupon?$coupon['satisty_price']:'';
            $result['discount_price'] = $coupon?$coupon['discount_price']:'';
            apiResponse('1','下单成功',$result);
        }else{
            apiResponse('0','下单失败');
        }
    }

    /**
     * 购物车下单准备接口
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID       m_id
     * 购物车信息   json串：[{"cart_id":"4"},{"cart_id":"10"}]
     */
    public function submitShopCartPage($request = array()){
        $member = $this ->searchMember($request['token']);
        //购物车信息
        if (empty($_POST['cart_json'])) {
            apiResponse('0', '请选择要买的商品');
        }
        $cart_list = json_decode($_POST['cart_json'], true);

        //将获得的json串转换
        if(empty($cart_list)){
            apiResponse('0', 'Json错误');
        }
        //查询用户是否有默认收货地址
        if(!$request['address_id']){
            $where['m_id'] = $member['id'];
            $where['is_default'] = 1;
            $where['status'] = array('neq',9);
            $address = M('Address') ->where($where) ->field('id as address_id, name, telephone, province, city, area, address_info, is_default') ->find();
        }else{
            $where['id'] = $request['address_id'];
            $where['status'] = array('neq',9);
            $address = M('Address') ->where($where) ->field('id as address_id, name, telephone, province, city, area, address_info, is_default') ->find();
        }

        if(empty($address)){
            $address['status'] = '2';
        }else{
            $address['province'] = M('Region') ->where(array('id'=>$address['province'])) ->getField('region_name');
            $address['city']     = M('Region') ->where(array('id'=>$address['city']))     ->getField('region_name');
            $address['area']     = M('Region') ->where(array('id'=>$address['area']))     ->getField('region_name');
            $address['status']   = '1';
        }
        $result['address'] = $address;
        //先查询商家ID并把重复的删掉
        $merchant = array();
        foreach($cart_list as $k => $v){
            unset($where);
            $where['id'] = $v['cart_id'];
            $where['status'] = array('neq',9);
            $merchant_list = M('Shopcart') ->where($where) ->field('master_id') ->find();
            if(!$merchant_list){
                continue;
            }
            $merchant[] = $merchant_list['master_id'];
        }
        if(!$merchant){
            $result['merchant'] = array();
            apiResponse('0','购物车信息有误',$result);
        }
        $merchant_id = array_unique($merchant);
        //根据商家ID进行商品分类（有点乱）总价格   总原价  总优惠
        $num_price = 0;
        $num_freight = 0;
        $num_result_total = 0;
        $delivery_price = 0;
        foreach($merchant_id as $k =>$v){
            unset($where);
            unset($freight);
            $where['id'] = $v;
            $where['status'] = 1;
            $merchant_info[$k] = M('Master') ->where($where) ->field('id as master_id, nickname') ->find();
            $total_num = 0;
            $total_price = 0;
            $total_result_price = 0;
            $freight = 0;
            foreach($cart_list as $key => $val){
                unset($where);
                $where['id'] = $val['cart_id'];
                $where['status'] = array('neq',9);
                $where['master_id'] = $v;
                $cart_info = M('Shopcart') ->where($where) ->field('id as card_id, goods_id, number') ->find();
                if(!$cart_info){
                    continue;
                }
                $goods_info[$key] = M('Goods') ->where(array('id'=>$cart_info['goods_id'],'frame'=>1,'status'=>1))
                    ->field('id as goods_id, goods_name, goods_type, goods_pic, freight, price') ->find();
                $goods_type = $this ->easyMysql('GoodsType','5',array('id'=>$goods_info[$key]['goods_type']),'','type_name');
                $goods_info[$key]['goods_type'] = $goods_type?$goods_type:'';

//                $goods_info[$key]['price'] = $this ->goodsPrice($goods_info[$key]['price'],1);

                $goods_pic = $goods_info[$key]['goods_pic'];
                $path = M('File') ->where(array('id'=>$goods_pic)) ->getField('path');
                $goods_info[$key]['goods_pic'] = $path?C("API_URL").$path:'';
                $goods_info[$key]['num'] = $cart_info['number'].'';
                $goods_info[$key]['price'] = $this ->goodsPrice($goods_info[$key]['price'], 1);
                $goods_info[$key]['total_price'] = $cart_info['number'] * $goods_info[$key]['price'] + $goods_info[$key]['freight'].'';
                $freight = $freight + $goods_info[$key]['freight'];
                $total_num = $total_num + $cart_info['number'].'';
                $total_price = $total_price + $goods_info[$key]['total_price'].'';
            }
            $goods_info = array_values($goods_info);
            $merchant_info[$k]['goods_info']   = $goods_info;
            $merchant_info[$k]['total_num']    = $total_num .'';
            $merchant_info[$k]['total_price']  = $total_price.'';
            $merchant_info[$k]['freight']  = $freight.'';
            $num_price = $num_price + $total_price;
            $num_freight = $num_freight + $merchant_info[$k]['freight'];
            unset($goods_info);
            unset($total_num);
            unset($total_price);
            unset($total_result_price);
            unset($delivery_price);
        }
        if(!$merchant_info){
            $result['merchant_info'] = array();
        }
        $merchant_info = array_values($merchant_info);
        $result['merchant_info'] = $merchant_info;
        $result['price']   = $num_price.'';
        $result['freight'] = $freight?$freight.'':'0.00';
        $result['balance'] = $member['balance'];

        apiResponse('1','',$result);
    }

    /**
     * @param array $request
     * 购物车提交订单
     * 传递参数的方式：post
     * 需要传递的参数：
     * 用户ID       m_id
     * 用户地址ID   address_id
     * 购物车信息   json串：[{"cart_id":"4"},{"cart_id":"10"}]
     * 优惠券信息   coupon_json     [{"master_id":"2","coupon_id":"1"},{"master_id":"1","coupon_id":"2"}]
     * 买家留言     message_json    [{"master_id":"2","message":"这是第一个"},{"master_id":"1","message":"这是第二个"}]
     */
    public function submitShopCart($request = array()){
        $member = $this ->searchMember($request['token']);
        //购物车ID不能为空   为一个json串
        if (empty($_POST['cart_json'])) {
            apiResponse('0','请选择下单的商品');
        }
        $cart_list = json_decode($_POST['cart_json'],true);

        //将获得的json串转换一下
        if(empty($cart_list)) {
            apiResponse('0', '商品参数选择错误');
        }

        if($_POST['message_json']) {
            $message_list = json_decode($_POST['message_json'],true);
            if(!$message_list){
                apiResponse('0', 'JSON错误');
            }
        }

        if($_POST['coupon_json']) {
            $coupon_list = json_decode($_POST['coupon_json'],true);
            if(!$coupon_list){
                apiResponse('0', 'JSON错误');
            }
        }
        //查询送货地点是否正确
        $where['id'] = $request['address_id'];
        $where['status'] = array('neq', 9);
        $address = M('Address')->where($where)->find();
        if(empty($address)){
            apiResponse('0', '地址信息有误');
        }
        $address['province'] = M('Region') ->where(array('id'=>$address['province'])) ->getField('region_name');
        $address['city']     = M('Region') ->where(array('id'=>$address['city']))     ->getField('region_name');
        $address['area']     = M('Region') ->where(array('id'=>$address['area']))     ->getField('region_name');

        $id = array();
        $total_price = 0;
        $list = array();
        $pay_price = 0;
        //判断选择结果的正确性
        foreach($cart_list as $k =>$v){
            $id[] = $v['cart_id'];
            unset($where);
            $where['id'] = $v['cart_id'];
            $where['status'] = array('neq',9);
            $cart = M('Shopcart') ->where($where) ->find();
            if(!$cart){
                continue;
            }

            $list[$k] = $cart;
            $merchant_list[] = $cart['master_id'];
            $goods = M('Goods') ->where(array('id'=>$cart['goods_id'])) ->find();
            $priceer = $goods['price'];
            $list[$k]['price'] = $priceer;
            $total_price = $total_price + $priceer*$cart['number'] + $goods['freight'];
        }
        $order_total_sn = 'a'.date('Ymd',time()).rand(1000000,9999999);
        $data['m_id']              = $member['id'];
        $data['address_id']        = $request['address_id'];
        $data['order_total_sn']    = $order_total_sn;
        $data['order_total_price'] = $total_price;
        $data['create_time']       = time();
        $order_group_id = M('OrderGroup')->data($data)->add();

        if(empty($order_group_id)){
            apiResponse('0','下单失败');
        }
        $merchant_id = array_unique($merchant_list);
        if(!$merchant_id){
            apiResponse('0','购物车信息有误');
        }
        $total_delivery_cost = 0;
        $order_list = array();
        foreach($merchant_id as $k =>$v){
            unset($goods_name);
            unset($trade_price);
            $order_sn = date('Ymd',time()).rand(1000000,9999999);
            $goods_info_serialization = array();
            $total = 0;
            $trade_price = 0;
            $goods_name = array();
            $goods_id = array();
            $real_price = 0;
            foreach($cart_list as $key => $val){
                unset($where);
                $where['id'] = $val['cart_id'];
                $where['master_id'] = $v;
                $where['status'] = array('neq',9);
                $cart = M('Shopcart') ->where($where) ->field('id as cart_id, m_id, goods_id, number') ->find();
                if(!$cart){
                    continue;
                }

                $goods_info = M('Goods') ->where(array('id'=>$cart['goods_id']))
                    ->field('id as goods_id, goods_name, goods_pic, freight, price, goods_type') ->find();

                if(!$goods_info){
                    continue;
                }else{
                    $goods_id[] = $cart['goods_id'];
                }

                $goods_price = $this ->goodsPrice($goods_info['price'],1);
//                $goods_type = $this ->easyMysql('GoodsType',5,array('id'=>$goods_info['goods_type']),'','type_name');
                //商品id  商品名称  商品图片  商品类别  商品价格  商品数量  商品总价  商品运费
                $goods_info_serialization['goods'][$key]['goodsDetail']['id'] = $goods_info['goods_id'];
                $goods_info_serialization['goods'][$key]['goodsDetail']['goods_name'] = $goods_info['goods_name'];
                $path = M('File') ->where(array('id'=>$goods_info['goods_pic'])) ->getField('path');
                $goods_info_serialization['goods'][$key]['goodsDetail']['goods_pic'] = $path;
//                $goods_info_serialization['goods'][$key]['goodsDetail']['goods_type'] = $goods_type;
                $goods_info_serialization['goods'][$key]['goodsDetail']['price'] = $goods_price;
                $goods_info_serialization['goods'][$key]['goodsDetail']['real_price'] = $goods_info['price'];
                $goods_info_serialization['goods'][$key]['num'] = $cart['number'];
                $goods_info_serialization['goods'][$key]['price'] = $goods_price * $cart['number'] + $goods_info['freight'];
                $goods_info_serialization['goods'][$key]['real_price'] = $goods_info['price'] * $cart['number'] + $goods_info['freight'];
                $goods_info_serialization['goods'][$key]['freight'] = $goods_info['freight']?$goods_info['freight']:'0.00';
                $goods_name[] = $goods_info['goods_name'];
                $total = $total + $goods_info_serialization['goods'][$key]['price'];
                $total_delivery_cost += $goods_info['freight'];
                $real_price = $real_price + $goods_info_serialization['goods'][$key]['real_price'];
            }

            unset($data);
            $data['m_id']        = $member['id'];
            $data['goods_id']    = implode(',',$goods_id);
            $data['master_id']   = $v;
            $data['order_total_id']  = $order_group_id;
            $data['address']     = serialize($address);
            $data['order_sn']    = $order_sn;
            $data['create_time'] = time();
            $data['total_price']  = $total;
            if(!empty($coupon_list)){
                foreach($coupon_list as $k1 =>$v1){
                    unset($coupon);
                    if($v1['master_id']==$v){
                        $coupon = $this ->easyMysql('MemberCoupon','3',array('id'=>$v1['coupon_id'],'status'=>0));
                        if(!$coupon){
                            apiResponse('0','优惠券信息有误');
                        }
                        $data['pay_price'] = $total - $coupon['discount_price'];
                        $coupon_info = $this ->setType('MemberCoupon',array('id'=>$v1['coupon_id'],'status'=>0),'status',1,1);
                    }
                }
            }else{
                $data['pay_price'] = $total;
            }

            $pay_price += $data['pay_price'];

            $data['freight'] = $total_delivery_cost;
            if(isset($message_list)){
                foreach($message_list as $k1 =>$v1){
                    if($v1['master_id']==$v){
                        $data['remark'] = $v1['message'];
                    }
                }
            }else{
                $data['remark'] = '';
            }
            $data['order_serialization'] = serialize($goods_info_serialization);
            $data['real_price'] = $real_price;
            $add_order_res = M('Order')->data($data)->add();

            $order_list[$k]['goods_name']     = implode(',',$goods_name);
            $order_list[$k]['order_sn']       = $order_sn;
            $order_list[$k]['order_price']    = $data['pay_price'].'';
            $order_list[$k]['discount_price'] = $coupon?$coupon['discount_price']:'';
            $order_list[$k]['satisty_price']  = $coupon?$coupon['satisty_price']:'';

            unset($total_delivery_cost);
            unset($where);
            $date['user_id']   = $member['id'];
            $date['user_type'] = 1;
            $date['type']      = 3;
            $data['object_id'] = $add_order_res;
            $date['headline']  = '宝阁消息';
            $date['content']   = '您订单号为'.$order_sn.'的订单已成功下单，请立即支付。';
            $date['create_time'] = time();
            $message = M('Message') ->add($date);

            $data['user_id']   = $v;
            $data['user_type'] = 2;
            $data['type']      = 3;
            $data['object_id'] = $add_order_res;
            $data['headline']  = '宝阁消息';
            $data['content']   = '您订单号为'.$order_sn.'的订单已成功下单。';
            $data['create_time'] = time();
            $message = M('Message') ->add($data);

            //极光推送
            //调用极光推送
            $con = $date['content'];
            $arr_j = $date['user_id'];
            $this->jPushToMember($arr_j,$add_order_res, $con ,$con);
        }
        unset($where);
        unset($data);
        $where['id'] = array('in',$id);
        $data['status'] = 9;
        $data['update_time'] = time();
        M('Shopcart')->where($where) ->data($data) ->save();

        $result['order_list_sn'] = $order_total_sn;
        $result['pay_price']     = $pay_price.'';
        $result['order_list']    = $order_list;

        apiResponse('1','下单成功',$result);
    }

    /**
     * 商品订单列表
     */
    public function orderList($request = array()){
        $member = $this ->searchMember($request['token']);

        //根据用户ID  查询订单
        $where['`order`.m_id'] = $member['id'];
        if($request['type'] != 5){
            $where['`order`.order_status'] = $request['type'];
        }elseif($request['type'] = ''){
            $where['`order`.order_status'] = 0;
        }
        $where['`order`.status'] = array('neq',9);
        $field = '`order`.id as order_id, master.nickname, `order`.order_sn, `order`.order_serialization, `order`.total_price, `order`.pay_price, `order`.order_status, `order`.freight';
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
            foreach ($goods_list['goods'] as $key => $value){
                $goods[$key]['goods_id']            = $value['goodsDetail']['id'];
                $goods[$key]['goods_pic']     = $value['goodsDetail']['goods_pic'] ? C('API_URL') . $value['goodsDetail']['goods_pic'] : '';
                $goods[$key]['goods_name']    = $value['goodsDetail']['goods_name'] ? $value['goodsDetail']['goods_name'] : '';
                $goods[$key]['price']         = $value['goodsDetail']['price'] ? $value['goodsDetail']['price'] : '0.00';
                $goods[$key]['goods_type']    = $value['goodsDetail']['goods_type'] ? $value['goodsDetail']['goods_type'] : '';
                $goods[$key]['num']           = $value['num'] ? $value['num'] : '0';
                $goods[$key]['freight']       = $value['freight']?$value['freight']:'0.00';
                $goods[$key]['total_price']   = $value['price'] ? $value['price'] : '0.00';
                $goods_num                    = $goods_num + $goods[$key]['num'];
            }
            $goods = array_values($goods);
            $order[$k]['goods_list'] = $goods;
            $order[$k]['goods_num']  = ''.$goods_num;
            unset($order[$k]['order_serialization']);
        }

        apiResponse('1','',$order);
    }

    /**
     * 商品订单详情
     */
    public function orderInfo($request = array()){
        $member = $this ->searchMember($request['token']);
        //根据已有条件查询订单详情
        $where['`order`.id'] = $request['order_id'];
        $where['`order`.m_id'] = $member['id'];
        $where['`order`.status'] = array('neq',9);
        $field = '`order`.id as order_id, `order`.order_sn, master.nickname, `order`.address, `order`.order_serialization, `order`.pay_time, `order`.pay_price, `order`.delivery, `order`.delivery_sn, `order`.deliver_time, `order`.deal_time, `order`.coupon, `order`.total_price, `order`.pay_price, `order`.remark, `order`.freight, `order`.order_status, `order`.remark, `order`.create_time';
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
        foreach ($goods_info['goods'] as $k => $v) {
            $goods[$i]['goods_id']         = $v['goodsDetail']['id'];
            $goods[$i]['goods_pic']  = $v['goodsDetail']['goods_pic'] ? C('API_URL').$v['goodsDetail']['goods_pic'] : '';
            $goods[$i]['goods_name'] = $v['goodsDetail']['goods_name'] ? $v['goodsDetail']['goods_name'] : '';
            $goods[$i]['price']      = $v['goodsDetail']['price'] ? $v['goodsDetail']['price'] : '0.00';
            $goods[$i]['goods_type'] = $v['goodsDetail']['goods_type'] ? $v['goodsDetail']['goods_type'] : '';
            $goods[$i]['num']        = $v['num'] ? $v['num'] : '0';
            $goods[$i]['freight']    = $v['freight'] ? $v['freight'].'' : '0.00';
            $goods[$i]['total_price'] = $v['price'] ? $v['price'].'' : '0.00';
            $freight = $freight + $goods[$i]['freight'];
            $i = $i + 1;
        }

        //获取快递信息
        if($order_info['delivery'] != '0'){
            $delivery_company = M('DeliveryCompany') ->where(array('id'=>$order_info['delivery']))->field('id as delivery_id, delivery_code, company_name') ->find();
//            $result['delivery_company'] = $delivery_company;
//            $result['key'] = '6c8d76a022fca426';
        }else{
//            $result['delivery_company'] = '';
//            $delivery_company = '';
//            $result['key'] = '';
        }


        $result['delivery_sn'] = $order_info['delivery_sn']?$order_info['delivery_sn']:'';
        $result['service_account'] = '8001';
        $result['service_pic'] = C('API_URL').'/Uploads/Member/service.png';
        //开始传入数据
        $order['goods_list']      = $goods;
        $order['address']         = $address;
        $order['order_status']    = $order_info['order_status'];
        $order['total_price']     = $order_info['total_price'];
        $order['pay_price']       = $order_info['pay_price'];
        $order['freight']         = $freight?$freight.'':'0.00';
        $order['order_sn']        = $order_info['order_sn'];
        $order['order_id']        = $order_info['order_id'];
        $order['remark']          = $order_info['remark'];
        $order['create_time']     = date('Y-m-d H:i',$order_info['create_time']);
        $order['pay_time']        = $order_info['pay_time'] != 0?date('Y-m-d H:i',$order_info['pay_time']):'';
        $order['deliver_time']    = $order_info['deliver_time'] != 0?date('Y-m-d H:i',$order_info['deliver_time']):'';
        $order['deal_time']       = $order_info['deal_time'] != 0?date('Y-m-d H:i',$order_info['deal_time']):'';
        $order['nickname']        = $order_info['nickname'];
        $order['delivery_sn']     = $order_info['delivery_sn']?$order_info['delivery_sn']:'';
//        $order['delivery_company'] = $result['company_name']?$result['company_name']:'';
        $order['company_name']    = $delivery_company?$delivery_company['company_name']:'';
//        $order['key']             = $result['key'];
        if(empty($order)){
            apiResponse('0','订单详情有误');
        }
        apiResponse('1','',$order);
    }

    /**
     * 取消订单     需要消息提醒
     */
    public function cancellOrder ($request = array()){
        $member = $this ->searchMember($request['token']);
        $where  = array('m_id'=>$member['id'],'id'=>$request['order_id'],'status'=>array('neq',9));
        $order  = $this ->easyMysql('Order','3',$where);
        if(!$order){
            apiResponse('0','订单信息有误');
        }
        $data['order_status'] = 5;
        $data['update_time'] = time();
        $order_res  = $this ->easyMysql('Order','2',$where,$data);
        if(!$order_res){
            apiResponse('0','取消订单失败');
        }
        unset($where);
        unset($data);
        $date['user_id'] = $member['id'];
        $date['user_type'] = 1;
        $date['type'] = 3;
        $date['object_id'] = $order['id'];
        $date['headline']  = '宝阁消息';
        $date['content']   = '您订单号为'.$order['order_sn'].'的订单已成功取消。';
        $date['create_time'] = time();
        $message = M('Message') ->add($date);

        if($member['push_message'] == 1){
            //极光推送
            //调用极光推送
            $con = $date['content'];
            $arr_j = $member['token'];
            $this->jPushToMember($arr_j,$order['id'], $con ,$con);
        }

        $data['user_id']     = $order['master_id'];
        $data['user_type']   = 2;
        $data['type']        = 3;
        $data['headline']    = '宝阁消息';
        $data['object_id']   = $order['id'];
        $data['content']     = '您订单号为'.$order['order_sn'].'的订单已被用户取消。';
        $data['create_time'] = time();
        $message = M('Message') ->add($data);
        $master = $this ->easyMysql('Master',3,array('id'=>$order['master_id']));
        //极光推送
        //调用极光推送
        $con = $data['content'];
        $arr_j = $master['token'];
        $this->jPushToMaster($arr_j,$order['id'], $con ,$con);

        apiResponse('1','取消订单成功');
    }

    /**
     * 确认收货     需要消息提醒
     */
    public function confirmOrder ($request = array()){
        $member = $this ->searchMember($request['token']);
        //查询订单状态   并改变订单状态
        $where['m_id'] = $member['id'];
        $where['id']   = $request['order_id'];
        $where['order_status'] = array('elt',2);
        $where['status'] = array('neq',9);
        $order_info = M('Order') ->where($where) ->find();
        if(!$order_info){
            apiResponse('0','订单信息有误');
        }
        if($order_info['goods_id'] == ''){
            apiResponse('0','商品信息有误');
        }
        $data['order_status'] = 3;
        $data['deal_time']    = time();
        $data['update_time']  = time();
        $result = M('Order') ->where($where) ->data($data) ->save();

        if(!$result){
            apiResponse('0','确认收货失败',$result);
        }

        $goods_info = explode(',',$order_info['goods_id']);
        foreach($goods_info as $k => $v){
            unset($goods);
            unset($member_integral);
            $goods = $this ->easyMysql('Goods','3',array('id'=>$v));
            $member_integral = $this ->setType('Member',array('id'=>$member['id']),'integral',$goods['integral'],1);
        }

        //给商家加钱
//        $goods_price = $this ->easyMysql('Config','5',array('id'=>94),'','value');
//        $master_price = ($order_info['pay_price']/100)*(100 - $goods_price);
        $master_status = $this ->setType('Master',array('id'=>$order_info['master_id']),'balance',$order_info['real_price'],1);

        //给双方发送信息
        unset($where);
        unset($data);
        $date['user_id'] = $member['id'];
        $date['user_type'] = 1;
        $date['type'] = 3;
        $date['object_id'] = $order_info['id'];
        $date['headline']  = '宝阁消息';
        $date['content']   = '您订单号为'.$order_info['order_sn'].'的订单已确认收货。';
        $date['create_time'] = time();
        $message = M('Message') ->add($date);

        if($member['push_message'] == 1){
            //极光推送
            //调用极光推送
            $con = $date['content'];
            $arr_j = $member['token'];
            $this->jPushToMember($arr_j,$order_info['id'], $con ,$con);
        }

        $data['user_id']     = $order_info['master_id'];
        $data['user_type']   = 2;
        $data['type']        = 3;
        $data['headline']    = '宝阁消息';
        $data['object_id']   = $order_info['id'];
        $data['content']     = '您订单号为'.$order_info['order_sn'].'的订单已确认收货。';
        $data['create_time'] = time();
        $message = M('Message') ->add($data);
        $master = $this ->easyMysql('Master',3,array('id'=>$order_info['master_id']));
        //极光推送
        //调用极光推送
        $con = $data['content'];
        $arr_j = $master['token'];
        $this->jPushToMaster($arr_j,$order_info['id'], $con ,$con);

        //给商家发一条收支明细
        unset($data);
        $data['user_type'] = 2;
        $data['user_id']   = $order_info['master_id'];
        $data['type']      = 1;
        $data['title']     = '宝阁收入';
        $data['price']     = $order_info['real_price'];
        $data['symbol']    = 1;
        $data['create_time'] = time();
        $detail = $this ->easyMysql('Detail','1','',$data);
        if(!$detail){
            apiResponse('0','写入明细失败');
        }
        //再发一条收支明细
        unset($data);
        $data['m_id'] = $member['id'];
        $data['master_id'] = $order_info['master_id'];
        $data['type'] = 2;
        $data['title'] = '宝阁收入';
        $data['price'] = $order_info['real_price'];
        $data['date'] = date('Y-m',time());
        $data['create_time'] = time();
        $paylog = $this ->easyMysql('PayLog','1','',$data);
        if(!$paylog){
            apiResponse('0','写入明细失败');
        }
        apiResponse('1','确认收货成功');
    }

    /**
     * 评价订单
     * 用户token     token
     * 订单id        order_id
     * 评价json      evaluate_json [{"goods_id":"49","evaluate_star":"3","logistics_star":"3","service_star":"3","content":"这个质量太一般","content_pic":"","anonymous","2"}]
     */
    public function evaluateOrder($request = array()){
        $member = $this ->searchMember($request['token']);
        //评价内容json串
        if(!$_POST['evaluate_json']){
            apiResponse('0','评价内容不能为空');
        }
        $evaluate = json_decode($_POST['evaluate_json'], true);

        //将获得的json串转换
        if(empty($evaluate)){
            apiResponse('0', 'Json错误');
        }

        $where['id'] = $request['order_id'];
        $where['status'] = array('neq',9);
        $where['order_status'] = 3;
        $order = $this ->easyMysql('Order','3',$where);
        if(!$order){
            apiResponse('0','订单信息有误');
        }
        $data['order_status'] = 4;
        $data['update_time'] = time();
        $result = $this ->easyMysql('Order','2',$where, $data);
        if(!$result){
            apiResponse('0','订单状态修改失败');
        }
        foreach($evaluate as $k => $v){
            unset($data);
            $data['order_id']       = $request['order_id'];
            $data['goods_id']       = $v['goods_id'];
            $data['m_id']           = $member['id'];
            $data['evaluate_star']  = $v['evaluate_star'];
            $data['logistics_star'] = $v['logistics_star'];
            $data['service_star']   = $v['service_star'];
            $data['content']        = $v['content'];
            $data['content_pic']    = $v['content_pic'];
            $data['anonymous']      = $v['anonymous'];
            $data['create_time']    = time();
            $res = $this ->easyMysql('OrderComment',1,'',$data);
        }

        //输入用户信息  商家信息  商家详情  商家明细
//        $message_member = $this ->addMessage(1,$member['id'],3,$request['order_id'],'宝阁消息','您已成功对订单号为'.$order['order_sn'].'的订单进行评价，该订单流程结束。');
//        $message_master = $this ->addMessage(2,$order['master_id'],3,$request['order_id'],'宝阁消息','您的订单号为'.$order['order_sn'].'的订单已被用户评价，该订单流程结束。');

        if($res){
            apiResponse('1','评价成功');
        }else{
            apiResponse('0','评价失败');
        }
    }

    /**
     * 上传图片
     * content_pic  多图上传
     */
    public function evaluatePicture ($request = array()){
        //发布图片不能为空w_r_pic0 w_r_pic1 w_r_pic2
        if($_FILES['content_pic']['name']){
            $res_pic = api('UploadPic/upload', array(array('save_path' => "Order")));
            $content_pic = array();
            foreach ($res_pic as $k => $value) {
                if($value['key'] == 'content_pic'){
                    $content_pic[] = $value['id'];
                }
            }
            $data['content_pic'] = implode(',',$content_pic);
        }

        apiResponse('1','',$data);
    }

    /**
     * 删除订单
     */
    public function deleteOrder ($request = array()){
        $member = $this ->searchMember($request['token']);
        $where  = array('id'=>$request['order_id'],'m_id'=>$member['id'],'status'=>array('neq',9));
        $order  = $this ->easyMysql('Order','3',$where);
        if(!$order){
            apiResponse('0','订单信息有误');
        }
        $data['status'] = 9;
        $data['update_time'] = time();
        $res = $this ->easyMysql('Order','2',$where,$data);
        if(!$res){
            apiResponse('0','删除订单失败');
        }

        apiResponse('1','删除订单成功');
    }

    /**
     * 申请退货   需要消息提醒
     */
    public function refundOrder ($request = array()){
        $member = $this ->searchMember($request['token']);
        $where  = array('id'=>$request['order_id'],'status'=>array('neq',9));
        $order  = $this ->easyMysql('Order',3,$where);
        if(!$order){
            apiResponse('0','订单信息有误');
        }
        $data['order_status'] = 6;
        $data['update_time'] = time();
        $res = $this ->easyMysql('Order','2',$where,$data);
        if(!$res){
            apiResponse('0','修改订单状态失败');
        }
        unset($where);
        unset($data);
        $data['m_id']       = $member['id'];
        $data['master_id']  = $order['master_id'];
        $data['order_id']   = $order['id'];
        $data['order_type'] = 2;
        $data['customer_type'] = $request['customer_type'];
        $data['goods_type'] = $request['goods_type'];
        $data['reason']     = $request['reason'];
        $data['price']      = $request['price'];
        $data['content']    = $request['content'];
        //发布图片不能为空w_r_pic0 w_r_pic1 w_r_pic2
        if($_FILES['picture']['name']){
            $res_pic = api('UploadPic/upload', array(array('save_path' => "Customer")));
            $picture = array();
            foreach ($res_pic as $k => $value) {
                if($value['key'] == 'picture'){
                    $picture[] = $value['id'];
                }
                $data['picture'] = implode(',',$picture);
            }
        }

        $data['customer_status'] = 0;
        $data['create_time'] = time();
        $customer = $this ->easyMysql('Customer','1','',$data);
        if(!$customer){
            apiResponse('0','申请退货失败');
        }
        //加入到消息模块
        if($request['customer_type'] == 1){
            $customer_type = '退货申请';
        }else{
            $customer_type = '退款申请';
        }
        if($request['goods_type'] == 1){
            $goods_type = '已收到货';
        }else{
            $goods_type = '未收到货';
        }
        $reason = $this ->easyMysql('Reason','5',array('id'=>$request['reason']),'','reason_name');

        unset($data);
        $data['user_type'] = 1;
        $data['user_id']  = $member['id'];
        $data['order_id'] = $request['order_id'];
        $data['order_type'] = 2;
        $data['headline'] = '买家发起了退货申请';
        $data['content']  = '发起了'.$customer_type.',退货状态：'.$goods_type.',原因：'.$reason.',金额：'.$request['price'].'元。';
        $data['picture']  = $picture?implode(',',$picture):'';
        $data['price']    = $request['price'];
        $data['create_time'] = time();
//        $data['status']   = 4;
        $message = $this ->easyMysql('MessageRefund',1,'',$data);

        //输入用户信息  商家信息  商家详情  商家明细
        $message_member = $this ->addMessage(1,$member['id'],3,$request['order_id'],'宝阁消息','您订单号为'.$order['order_sn'].'的订单已成功发起'.$customer_type.'，请随时关注本订单退货动态。');

        if($member['push_message'] == 1){
            //极光推送
            //调用极光推送
            $con = '您订单号为'.$order['order_sn'].'的订单已成功发起'.$customer_type.'，请随时关注本订单退货动态。';
            $arr_j = $member['token'];
            $this->jPushToMember($arr_j, $request['order_id'], $con ,$con);
        }

        $message_master = $this ->addMessage(2,$order['master_id'],3,$request['order_id'],'宝阁消息','您订单号为'.$order['order_sn'].'的的订单已被提交'.$customer_type.'，请您进行相关操作。');

        //极光推送
        //调用极光推送
        $master = $this ->easyMysql('Master',3,array('id'=>$order['master_id']));
        $con = '您订单号为'.$order['order_sn'].'的的订单已被提交'.$customer_type.'，请您进行相关操作。';
        $arr_j = $master['token'];
        $this->jPushToMaster($arr_j, $request['order_id'], $con ,$con);

        if(!$message){
            apiResponse('0','写入数据有误');
        }
        apiResponse('1','提交申请成功');
    }

    /**
     * 取消申请   需要消息提醒
     */
    public function cancellRefund ($request = array()){
        $member = $this ->searchMember($request['token']);
        $where['id']     = $request['order_id'];
        $where['status'] = array('neq',9);
        $where['m_id']   = $member['id'];
        $order = $this ->easyMysql('Order','3',$where);
        if(!$order){
            apiResponse('0','订单信息有误');
        }
        $data['order_status'] = 1;
        $data['update_time']  = time();
        $order_res = $this ->easyMysql('Order','2',$where,$data);
        if(!$order_res){
            apiResponse('0','修改订单状态有误');
        }

        unset($data);
        unset($where);
        $where['order_id'] = $request['order_id'];
        $where['order_type'] = 2;
        $refund = $this ->easyMysql('Customer',3,$where);
        if(!$refund){
            apiResponse('0','申请信息有误');
        }
        $data['customer_status'] = 6;
        $data['update_time'] = time();
        $res = $this ->easyMysql('Customer',2,$where,$data);
        if(!$res){
            apiResponse('0','取消申请失败');
        }

        unset($data);
        $data['user_type'] = 1;
        $data['user_id']  = $member['id'];
        $data['order_id'] = $request['order_id'];
        $data['order_type'] = 2;
        $data['headline'] = '买家取消退货申请';
        $data['content']  = '买家已取消退货申请。';
        $data['create_time'] = time();
        $data['status']   = 6;
        $message = $this ->easyMysql('MessageRefund',1,'',$data);

        //输入用户信息  商家信息  商家详情  商家明细
        $message_member = $this ->addMessage(1,$member['id'],3,$request['order_id'],'宝阁消息','您订单号为'.$order['order_sn'].'的已成功取消退货。');
        $master = $this ->easyMysql('Master',3,array('id'=>$order['master_id']));

        if($member['push_message'] == 1){
            //极光推送
            //调用极光推送
            $con = '您订单号为'.$order['order_sn'].'的已成功取消退货。';
            $arr_j = $member['token'];
            $this->jPushToMember($arr_j, $request['order_id'], $con ,$con);
        }

        $message_master = $this ->addMessage(2,$order['master_id'],3,$request['order_id'],'宝阁消息','您订单号为'.$order['order_sn'].'的订单已成功取消退货。');

        //极光推送
        //调用极光推送
        $con = '您订单号为'.$order['order_sn'].'的订单已成功取消退货。';
        $arr_j = $master['token'];
        $this->jPushToMaster($arr_j, $request['order_id'], $con ,$con);

        apiResponse('1','取消申请成功');
    }

    /**
     * 填写运单信息   需要消息提醒
     */
    public function chooseExpress($request = array()){
        $member = $this ->searchMember($request['token']);
        $where['id'] = $request['order_id'];
        $where['status'] = array('neq',9);
        $where['order_status'] = 6;
        $order = $this ->easyMysql('Order','3',$where);
        if(!$order){
            apiResponse('0','订单信息有误');
        }
        unset($where);
        $where['order_id']   = $request['order_id'];
        $where['order_type'] = 2;
        $where['customer_status']     = 2;
        $refund = $this ->easyMysql('Customer','3',$where);
        if(!$refund){
            apiResponse('0','订单信息有误');
        }
        $data['delivery'] = $request['delivery'];
        $data['number']   = $request['number'];
        $data['update_time'] = time();
        $data['customer_status']   = 3;
        $res = $this ->easyMysql('Customer','2',$where,$data);
        if(!$res){
            apiResponse('0','填写信息成功');
        }
        unset($data);
        $where = array('id'=>$request['delivery']);
        //company_name
        $delivery = $this ->easyMysql('DeliveryCompany',3,$where);
        $data['user_type'] = 1;
        $data['user_id']  = $member['id'];
        $data['order_id'] = $request['order_id'];
        $data['order_type'] = 2;
        $data['headline'] = '买家已经退货';
        $data['content']  = '买家退货，物流公司：'.$delivery['company_name'].',物流单号：'.$request['number'].',快递方式：快递。';
        $data['create_time'] = time();
        $data['status']   = 3;
        $message = $this ->easyMysql('MessageRefund',1,'',$data);
        if(!$message){
            apiResponse('0','写入数据有误');
        }

        //输入用户信息  商家信息  商家详情  商家明细
        $message_member = $this ->addMessage(1,$member['id'],3,$request['order_id'],'宝阁消息','您订单号为'.$order['order_sn'].'的已填写运单信息，等待对方确认。');
        $master = $this ->easyMysql('Master',3,array('id'=>$order['master_id']));

        if($member['push_message'] == 1){
            //极光推送
            //调用极光推送
            $con = '您订单号为'.$order['order_sn'].'的已填写运单信息，等待对方确认。';
            $arr_j = $member['token'];
            $this->jPushToMember($arr_j, $request['order_id'], $con ,$con);
        }

        $message_master = $this ->addMessage(2,$order['master_id'],3,$request['order_id'],'宝阁消息','您订单号为'.$order['order_sn'].'的的订单已填写运单信息，请及时查收。');

        //极光推送
        //调用极光推送
        $con = '您订单号为'.$order['order_sn'].'的的订单已填写运单信息，请及时查收。';
        $arr_j = $master['token'];
        $this->jPushToMaster($arr_j, $request['order_id'], $con ,$con);

        apiResponse('1','退货申请成功');
    }

    /**
     * 快递列表   不需要消息提醒
     */
    public function expressList(){
        $delivery = $this ->easyMysql('DeliveryCompany','4',array(),'','id as delivery_id, delivery_code, company_name');
        if(!$delivery){
            $delivery = array();
        }
        apiResponse('1','',$delivery);
    }

    /**
     * 协商退货界面   不需要消息提醒
     */
    public function refundOrderPage($request = array()){
        $member = $this ->searchMember($request['token']);
        $which  = array('order_id'=>$request['order_id'], 'order_type'=>2, 'status'=>array('neq',9));
        $customer = $this ->easyMysql('Customer','3',$which);

        $where['order_id'] = $request['order_id'];
        $where['order_type'] = 2;
        $field  = 'id as refund_id, user_type, headline, content, picture, create_time, status, price';
        $order  = 'create_time asc';
        $message = $this ->easyMysql('MessageRefund',4,$where,'',$field,$order);
        if(!$message){
            $message = array();
        }else{
            foreach($message as $k => $v){
                unset($picture);
                $message[$k]['create_time'] = date('Y-m-d H:i', $v['create_time']);
                if($v['picture'] != ''){
                    $picture = array();
                    $pic = explode(',',$v['picture']);
                    foreach($pic as $key => $val){
                        unset($photo);
                        $photo = $this ->searchPhoto($val);
                        $picture[$key]['pic'] = $photo?$photo:'';
                    }
                }
                $message[$k]['picture'] = $picture?$picture:array();
                $message[$k]['customer_type'] = $customer['customer_type'];
            }
        }

        apiResponse('1','',$message);
    }

    /**
     * 订单支付页面   不需要消息提醒
     */
    public function orderListPage($request = array()){
        $member = $this ->searchMember($request['token']);
        $where  = array('id'=>$request['order_id'],'status'=>array('neq',9));
        $order  = $this ->easyMysql('Order','3',$where);
        if(!$order){
            apiResponse('0','订单信息有误');
        }
        $result['balance'] = $member['balance'];
        $result['order_sn'] = $order['order_sn'];
        $result['pay_price'] = $order['pay_price'];
        $order_info = unserialize($order['order_serialization']);
//        apiResponse('error','',$order_info);
        $goods_name = array();
        foreach($order_info['goods'] as $k => $v){

            $goods_name[] = $v['goodsDetail']['goods_name'];
        }
        if(!empty($goods_name)){
            $result['goods_name'] = implode(',',$goods_name);
        }else{
            $result['goods_name'] = '';
        }

        apiResponse('1','',$result);
    }
}