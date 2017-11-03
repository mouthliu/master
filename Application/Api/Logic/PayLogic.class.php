<?php
namespace Api\Logic;
use Common\Service\RestService;
/**
 * Class OrderLogic
 * @package Api\Logic
 * 订单模块
 */
class PayLogic extends BaseLogic{
    public function _initialize(){
        parent::_initialize(); // TODO: Change the autogenerated stub
    }

    /**
     * 悬赏订单余额支付
     * 用户token
     */
    public function rewardPayOrder($request = array()){
        $member = searchMember($request['token']);
        if($member['balance'] < $request['price']){
            apiResponse('0','您的余额不足');
        }
        $where = array('id'=>$request['rorder_id'],'m_id'=>$member['id'],'status'=>0, 'pay_status'=>0, 'r_order_status'=>0);
        $order = $this ->easyMysql('RewardOrder',3,$where);
        if(!$order){
            apiResponse('0','订单信息有误');
        }
        $res = $this ->setType('Member',array('id'=>$member['id']),'balance',$request['price'],2);
        if(!$res){
            apiResponse('0','余额支付失败');
        }
        $data['pay_type'] = 4;
        $data['pay_status'] = 1;
        $data['status'] = 1;
        $data['r_order_status'] = 1;
        $data['update_time'] = time();
        $result = $this ->easyMysql('RewardOrder',2,$where,$data);
        if(!$result){
            apiResponse('0','支付失败');
        }

        //添加账单明细
        unset($data);
        $data['user_type'] = 1;
        $data['user_id']   = $order['m_id'];
        $data['type']      = 2;
        $data['title']     = '悬赏订单——余额支付';
        $data['price']     = $request['price'];
        $data['symbol']    = 2;
        $data['create_time'] = time();
        M('Detail')->data($data)->add();

        //写入一条订单信息
        $dat['user_type'] = 1;
        $dat['user_id']   = $order['m_id'];
        $dat['type']      = 2;
        $dat['object_id'] = $order['id'];
        $dat['headline']   = '悬赏消息';
        $dat['content'] = '您的悬赏订单支付成功，请等待大师答疑解惑';
        $dat['create_time'] = time();
        $message = $this ->easyMysql('Message','1','',$dat);

        if($order['master_id'] != 0){
            //写入一条订单信息
            $dat['user_type'] = 2;
            $dat['user_id']   = $order['master_id'];
            $dat['type']      = 2;
            $dat['object_id'] = $order['id'];
            $dat['headline']   = '悬赏消息';
            $dat['content'] = '有一条指定您回答的悬赏订单已经付款，请您答疑解惑';
            $dat['create_time'] = time();
            $message = M('Message') ->data($dat) ->add();
        }

        apiResponse('1','支付成功');
    }

    /**
     * 围观支付余额支付
     */
    public function watchPayOrder($request = array()){
        $member = searchMember($request['token']);
        if($member['balance'] < $request['price']){
            apiResponse('0','您的余额不足');
        }
        $where = array('id'=>$request['watch_id'],'status'=>0, 'pay_status'=>0);
        $order = $this ->easyMysql('Watch',3,$where);
        if(!$order){
            apiResponse('0','订单信息有误');
        }
        $where = array('id'=>$order['rorder_id'],'status'=>array('neq',9));
        $rorder = $this ->easyMysql('RewardOrder',3,$where);
        if(!$rorder){
            apiResponse('0','订单信息有误');
        }

        $where = array('r_o_id'=>$order['rorder_id'], 'is_adopt'=>1, 'status'=>array('neq',9));
        $answer = $this ->easyMysql('RorderAnswer',3,$where);
        if(!$answer){
            apiResponse('0','该悬赏暂无回复');
        }

        $res = $this ->setType('Member',array('id'=>$member['id']),'balance',$request['price'],2);
        if(!$res){
            apiResponse('0','余额支付失败');
        }
        $data['pay_type'] = 4;
        $data['pay_status'] = 1;
        $data['status'] = 1;
        $data['update_time'] = time();
        $result = $this ->easyMysql('Watch',2,$where,$data);
        if(!$result){
            apiResponse('0','支付失败');
        }

        //添加账单明细
        unset($data);
        $data['user_type'] = 1;
        $data['user_id']   = $order['m_id'];
        $data['type']      = 2;
        $data['title']     = '围观支付——余额支付';
        $data['price']     = $request['price'];
        $data['symbol']    = 2;
        $data['create_time'] = time();
        M('Detail')->data($data)->add();

        //写入一条订单信息
        $dat['user_type'] = 1;
        $dat['user_id']   = $order['m_id'];
        $dat['type']      = 2;
        $dat['object_id'] = $order['id'];
        $dat['headline']   = '悬赏消息';
        $dat['content'] = '您已成功围观该悬赏信息，请等待大师答疑解惑';
        $dat['create_time'] = time();
        $message = $this ->easyMysql('Message','1','',$dat);

        $member_price = $this ->setType('Member',array('id'=>$rorder['m_id']),'balance',($request['price']/2),1);
        if($member_price){
            $detail = $this ->addDetail(1,$rorder['m_id'],1,'围观收入',1,($request['price']/2));
        }
        $master_price = $this ->setType('Master',array('id'=>$answer['master_id']),'balance',($request['price']/2),1);
        if($master_price){
            $detail = $this ->addDetail(2, $answer['master_id'], 1, '围观收入', 1, ($request['price']/2));
            $pay_log = $this ->addPayLog($rorder['m_id'], $answer['master_id'], 3, '用户围观收入', ($request['price']/2));
        }

        //围观人数加1
        $rorder_watch = $this ->setType('RewardOrder',array('id'=>$order['rorder_id']),'watch_man',1,1);

        apiResponse('1','支付成功');
    }

    /**
     * 服务订单余额支付
     */
    public function servicePayOrder($request = array()){
        $member = searchMember($request['token']);
        if($member['balance'] < $request['price']){
            apiResponse('0','您的余额不足');
        }
        $where = array('id'=>$request['sorder_id'],'status'=>0, 'pay_status'=>0);
        $order = $this ->easyMysql('ServiceOrder',3,$where);
        if(!$order){
            apiResponse('0','订单信息有误');
        }
        $res = $this ->setType('Member',array('id'=>$member['id']),'balance',$request['price'],2);
        if(!$res){
            apiResponse('0','余额支付失败');
        }
        $data['pay_type'] = 4;
        $data['pay_status'] = 1;
        $data['status'] = 1;
        $data['s_order_status'] = 1;
        $data['update_time'] = time();
        $result = $this ->easyMysql('ServiceOrder',2,$where,$data);
        if(!$result){
            apiResponse('0','支付失败');
        }

        //添加账单明细
        unset($data);
        $data['user_type'] = 1;
        $data['user_id']   = $order['m_id'];
        $data['type']      = 2;
        $data['title']     = '服务订单——余额支付';
        $data['price']     = $request['price'];
        $data['symbol']    = 2;
        $data['create_time'] = time();
        M('Detail')->data($data)->add();

        apiResponse('1','支付成功');
    }

    /**
     * 红包支付余额支付
     */
    public function redPayOrder($request = array()){
        $member = searchMember($request['token']);
        if($member['balance'] < $request['price']){
            apiResponse('0','您的余额不足');
        }
        $where = array('id'=>$request['red_id'],'status'=>0, 'pay_status'=>0);
        $order = $this ->easyMysql('RedPackage',3,$where);
        if(!$order){
            apiResponse('0','订单信息有误');
        }
        $res = $this ->setType('Member',array('id'=>$member['id']),'balance',$request['price'],2);
        if(!$res){
            apiResponse('0','余额支付失败');
        }
        $data['pay_type'] = 4;
        $data['pay_status'] = 1;
        $data['status'] = 1;
        $data['update_time'] = time();
        $result = $this ->easyMysql('RedPackage',2,$where,$data);
        if(!$result){
            apiResponse('0','支付失败');
        }

        //添加账单明细
        unset($data);
        $data['user_type'] = 1;
        $data['user_id']   = $order['m_id'];
        $data['type']      = 2;
        $data['title']     = '服务订单——余额支付';
        $data['price']     = $request['price'];
        $data['symbol']    = 2;
        $data['create_time'] = time();
        M('Detail')->data($data)->add();

        apiResponse('1','支付成功');
    }

    /**
     * 商品订单余额支付
     */
    public function orderPayOrder($request = array()){
        $member = $this ->searchMember($request['token']);
        $first_letter = substr($request['order_sn'],0,1);
        $bool = is_numeric($first_letter);
        if($bool == 0){
            //查询订单总价
            $order_list = M('OrderGroup') ->where(array('order_total_sn'=>$request['order_sn'])) ->field('id as order_total_id, order_total_sn, order_total_price') ->find();
            if(!$order_list){
                apiResponse('0','订单信息有误');
            }

            //查询子订单信息
            $where['order_total_id']    = $order_list['order_total_id'];
            $where['status']            = 0;
            $where['order_status']      = 0;
            $order = M('Order') ->where($where) ->field('id as order_id, order_sn, m_id, master_id, pay_price') ->select();
            if(!$order){
                apiResponse('0','该订单信息有误');
            }

            if($member['balance'] < $order_list['order_total_price']){
                apiResponse('0','您的余额不足');
            }

            unset($where);
            $data['pay_status'] = 1;
            $data['pay_type']   = 4;
            $data['status']     = 1;
            $data['update_time'] = time();
            $result =  M('OrderGroup') ->where(array('order_total_sn'=>$request['order_sn'])) ->data($data) ->save();
            unset($data);

            $res = $this ->setType('Member',array('id'=>$member['id']),'balance',$order_list['order_total_price'],2);
            if(!$res){
                apiResponse('0','付款失败');
            }
            //用户写入详情
            $data['user_type'] = 1;
            $data['user_id']   = $member['id'];
            $data['type']      = 2;
            $data['title']     = '宝阁订单支付';
            $data['price']     = $order_list['order_total_price'];
            $data['symbol']    = 2;
            $data['create_time'] = time();
            $result = M('Detail') ->add($data);
            //修改订单状态并写入大师详情
            foreach($order as $k => $v){
                unset($where);
                unset($data);
                $where['id'] = $v['order_id'];
                $data['order_status'] = 1;
                $data['pay_status'] = 1;
                $data['pay_type'] = 4;
                $data['update_time'] = time();
                $data['status'] = 1;
                $data['pay_time'] = time();
                $result_data = M('Order') ->where($where) ->data($data) ->save();
                if($result_data){
                    continue;
                }

                unset($data);
                unset($where);
                $data['user_type'] = 1;
                $data['user_id']   = $member['id'];
                $data['type']      = 2;
                $data['title']     = '宝阁订单支付';
                $data['price']     = $v['pay_price'];
                $data['symbol']    = 2;
                $data['create_time'] = time();
                $result = M('Detail') ->add($data);

                //输入用户信息  商家信息  商家详情  商家明细
                $message_member = $this ->addMessage(1,$member['id'],3,$v['order_id'],'宝阁消息','您订单号为'.$v['order_sn'].'的订单已成功支付，请等待商家操作。');
                if($member['push_message'] == 1){
                    //极光推送
                    //调用极光推送
                    $con = '您订单号为'.$v['order_sn'].'的订单已成功支付，请等待商家操作。';
                    $arr_j = $member['token'];
                    $this->jPushToMember($arr_j, $v['order_id'], $con ,$con);
                }

                $message_master = $this ->addMessage(2,$v['master_id'],3,$v['order_id'],'宝阁消息','您订单号为'.$v['order_sn'].'的的订单已成功支付，请进行相关操作。');

                $master = $this ->easyMysql('Master',3,array('id'=>$v['master_id']));
                //极光推送
                //调用极光推送
                $con = '您订单号为'.$v['order_sn'].'的的订单已成功支付，请进行相关操作。';
                $arr_j = $master['token'];
                $this->jPushToMaster($arr_j, $v['order_id'], $con ,$con);
            }
        }else{
            unset($where);
            unset($data);
            $where['order_sn']      = $request['order_sn'];
            $where['status']        = 0;
            $where['order_status']  = 0;
            $order_list = M('Order') ->where($where) ->field('id as order_id, order_sn, m_id, master_id, pay_price') ->find();
            if(!$order_list){
                apiResponse('0','该订单详情有误');
            }

            unset($where);
            unset($data);
            if($member['balance'] < $order_list['pay_price']){
                apiResponse('','您的余额不足');
            }
            $res = $this ->setType('Member',array('id'=>$member['id']),'balance',$order_list['pay_price'],2);
            if(!$res){
                apiResponse('0','付款失败');
            }
            unset($where);
            unset($data);
            $where['order_sn'] = $request['order_sn'];
            $data['order_status'] = 1;
            $data['pay_status'] = 1;
            $data['pay_type'] = 4;
            $data['status'] = 1;
            $data['pay_time'] = time();
            $data['update_time'] = time();
            $result_data = M('Order') ->where($where) ->data($data) ->save();
            if(!$result_data){
                apiResponse('0','付款失败');
            }
            unset($data);
            unset($where);
            $data['user_type'] = 1;
            $data['user_id']   = $member['id'];
            $data['type']      = 2;
            $data['title']     = '宝阁订单支付';
            $data['price']     = $order_list['pay_price'];
            $data['symbol']    = 2;
            $data['create_time'] = time();
            $result = M('Detail') ->add($data);

            //输入用户信息  商家信息  商家详情  商家明细
            $message_member = $this ->addMessage(1,$member['id'],3,$order_list['order_id'],'宝阁消息','您订单号为'.$order_list['order_sn'].'的订单已成功支付，请等待商家操作。');
            if($member['push_message'] == 1){
                //极光推送
                //调用极光推送
                $con = '您订单号为'.$order_list['order_sn'].'的订单已成功支付，请等待商家操作。';
                $arr_j = $member['token'];
                $this->jPushToMember($arr_j, $order_list['order_id'], $con ,$con);
            }

            $master = $this ->easyMysql('Master',3,array('id'=>$order_list['master_id']));
            $message_master = $this ->addMessage(2,$order_list['master_id'],3,$order_list['order_id'],'宝阁消息','您订单号为'.$order_list['order_sn'].'的的订单已成功支付，请进行相关操作。');
            //极光推送
            //调用极光推送
            $con = '您订单号为'.$order_list['order_sn'].'的的订单已成功支付，请进行相关操作。';
            $arr_j = $master['token'];
            $this->jPushToMaster($arr_j, $order_list['order_id'], $con ,$con);
        }

        apiResponse('1','付款成功');
    }
}