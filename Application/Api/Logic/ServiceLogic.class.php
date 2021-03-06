<?php
namespace Api\Logic;
use Common\Service\RestService;
/**
 * Class OrderLogic
 * @package Api\Logic
 * 订单模块
 */
class ServiceLogic extends BaseLogic{
    public function _initialize(){
        parent::_initialize(); // TODO: Change the autogenerated stub
    }

    /**
     * 大师服务订单列表
     */
    public function serviceOrderList($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where['sorder.master_id']   = $master['id'];
        if($request['type'] != 3){
            $where['sorder.s_order_status'] = $request['type'];
        }
        $where['sorder.status'] = array('neq',10);
        $field = 'sorder.id as sorder_id, sorder.order_sn, sorder.create_time, sorder.s_order_status, service.title, service.picture';
        $order = 'sorder.create_time desc';
        $order_list = D('ServiceOrder') ->selectServiceOrder($where, $field, $order, $request['p']);
        if(!$order_list){
            $order_list = array();
        }else{
            foreach($order_list as $k => $v){
                $order_list[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
                $picture = $this ->searchPhoto($v['picture']);
                $order_list[$k]['picture'] = $picture?$picture:'';
            }
        }

        apiResponse('1','',$order_list);
    }

    /**
     * 大师服务订单详情
     */
    public function serviceOrderInfo($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where  = array('sorder.id'=>$request['sorder_id'], 'sorder.status'=>array('neq',10),'sorder.master_id'=>$master['id']);
        $field  = 'sorder.id as sorder_id , sorder.order_sn, sorder.create_time, sorder.s_order_status, sorder.name, sorder.sex, sorder.birthday, sorder.content, sorder.res_price as price, service.title, master.nickname as mas_nickname, master.head_pic as mas_head_pic, master.field_id, master.auth_status, master.social_id, master.introduction, master.score, region.region_name, master.easemob_account as mas_easemob_account, member.nickname as mem_nickname, member.head_pic as mem_head_pic, member.easemob_account as mem_easemob_account';
        $order_list = D('ServiceOrder') ->selectServiceOrder($where, $field, '', '', '', 1);
        if(!$order_list){
            apiResponse('0','服务订单详情有误');
        }
        $order_list['create_time'] = date('Y-m-d H:i:s',$order_list['create_time']);
        $mas_head_pic = $this ->searchPhoto($order_list['mas_head_pic']);
        $order_list['mas_head_pic'] = $mas_head_pic?$mas_head_pic:C('API_URL').'/Uploads/Master/default.png';
        $mem_head_pic = $this ->searchPhoto($order_list['mem_head_pic']);
        $order_list['mem_head_pic'] = $mem_head_pic?$mem_head_pic:C('API_URL').'/Uploads/Member/default.png';
        if($order_list['social_id'] != 0){
            $order_list['social_status'] = '1';
        }else{
            $order_list['social_status'] = '2';
        }

        if(!empty($order_list['field_id'])){
            $field_list = explode(',',$order_list['field_id']);
            $field_info = array();
            foreach($field_list as $key =>$val){
                $field_name = $this ->easyMysql('Field',3,array('id'=>$val,'status'=>array('neq',9)),'','id as field_id, field_name');
                if(!empty($field_name)){
                    $field_info[] = $field_name;
                }
            }
        }
        $order_list['field_info'] = $field_info?$field_info:array();
        $order_num = $this ->serviceOrderNum($master['id']);
        $order_list['order_num'] = $order_num?$order_num.'':'0';

        apiResponse('1','',$order_list);
    }

    /**
     * 聊天
     */
    public function chat($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where  = array('id'=>$request['sorder_id'],'master_id'=>$master['id'],'status'=>array('neq',10));
        $sorder = $this ->easyMysql('ServiceOrder','3',$where);
        if(!$sorder){
            apiResponse('0','订单详情有误');
        }
        $data['s_order_status'] = 2;
        $data['update_time']    = time();
        $res    = $this ->easyMysql('ServiceOrder','2',$where,$data);
        if(!$res){
            apiResponse('0','状态修改失败');
        }
        apiResponse('1','');
    }

    /**
     * 删除订单
     */
    public function deleteOrder($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where  = array('id'=>$request['sorder_id'],'master_id'=>$master['id'],'status'=>array('neq',10));
        $sorder = $this ->easyMysql('ServiceOrder','3',$where);
        if(!$sorder){
            apiResponse('0','订单详情有误');
        }
        $data['status'] = 10;
        $data['update_time'] = time();
        $res = $this ->easyMysql('ServiceOrder','2',$where,$data);
        if(!$res){
            apiResponse('0','删除失败');
        }
        apiResponse('1','删除成功');
    }

    /**
     * 同意退款或者拒绝退款
     */
    public function refundType($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where  = array('id'=>$request['sorder_id'],'master_id'=>$master['id'],'status'=>array('neq',10));
        $sorder = $this ->easyMysql('ServiceOrder','3',$where);
        if(!$sorder){
            apiResponse('0','订单详情有误');
        }
        if($request['type'] == 1){
            $data['s_order_status'] = 7;
        }else{
            $data['s_order_status'] = 8;
        }
        $data['update_time'] = time();
        $result = $this ->easyMysql('ServiceOrder','2',$where,$data);
        if(!$result){
            apiResponse('0','操作失败');
        }

        $where = array('order_id'=>$request['sorder_id'], 'order_type'=>1, 'master_id'=>$master['id']);
        $customer = $this ->easyMysql('Customer',3,$where);
        if(!$customer){
            apiResponse('0','退款信息有误');
        }

        if($request['type'] == 1){
            $dat['status'] = 1;

            unset($data);
            $data['user_type'] = 2;
            $data['user_id']   = $sorder['master_id'];
            $data['type']      = 2;
            $data['title']     = '服务订单退款';
            $data['price']     = $request['price'];
            $data['symbol']    = 2;
            $data['create_time'] = time();
            $res = $this ->easyMysql('Detail','1','',$data);
            if(!$res){
                apiResponse('0','大师写入详情失败');
            }
            $member_type = $this ->setType('Member',array('id'=>$sorder['m_id']),'balance',$request['price'],1);
            if(!$member_type){
                apiResponse('0','买家赠款失败');
            }
            unset($data);
            $data['user_type'] = 1;
            $data['user_id']   = $sorder['m_id'];
            $data['type']      = 1;
            $data['title']     = '服务订单退款';
            $data['price']     = $request['price'];
            $data['symbol']    = 1;
            $data['create_time'] = time();
            $res_data = $this ->easyMysql('Detail','1','',$data);
            if(!$res_data){
                apiResponse('0','用户写入详情失败');
            }
            //写入反馈信息
            unset($data);
            $data['user_type'] = 2;
            $data['user_id']   = $master['id'];
            $data['order_id']  = $request['sorder_id'];
            $data['order_type'] = 1;
            $data['headline']  = '大师同意申请';
            $data['content']   = '大师已同意申请，并已打款';
            $data['create_time'] = time();
            $data['status']    = 1;
            $message = $this ->easyMysql('MessageRefund','1','',$data);

            //输入用户信息  商家信息  商家详情  商家明细
//            $message_member = $this ->addMessage(1,$sorder['m_id'],1,$request['sorder_id'],'服务消息','大师已同意您的订单退款要求，并已同意退款。');
//            $message_master = $this ->addMessage(2,$sorder['master_id'],1,$request['sorder_id'],'服务消息','您已同意该用户的订单退款要求。');
        }else{
            $dat['status'] = 2;
            //写入反馈信息
            unset($data);
            $data['user_type'] = 2;
            $data['user_id']   = $master['id'];
            $data['order_id']  = $request['sorder_id'];
            $data['order_type'] = 1;
            $data['headline']  = '大师拒绝申请';
            $data['content']   = '大师已拒绝该退款申请';
            $data['price']     = $request['price'];
            $data['create_time'] = time();
            $data['status']    = 2;
            $message = $this ->easyMysql('MessageRefund','1','',$data);

            //输入用户信息  商家信息  商家详情  商家明细
//            $message_member = $this ->addMessage(1,$sorder['m_id'],1,$request['sorder_id'],'服务消息','大师已拒绝您的订单退款要求。');
//            $message_master = $this ->addMessage(2,$sorder['master_id'],1,$request['sorder_id'],'服务消息','您已拒绝该用户的订单退款要求。');
        }

        $dat['update_time'] = time();
        $result_data = $this ->easyMysql('Customer',2,$where,$dat);

        apiResponse('1','操作成功');
    }

    /**
     * 退款详情
     */
    public function refundPage($request = array()){
        $where['order_type'] = 1;
        $where['order_id']   = $request['sorder_id'];
        $field = 'id as refund_id, user_type, headline, order_id, content, picture, create_time, price, status';
        $order = 'create_time asc';
        $result = $this ->easyMysql('MessageRefund','4',$where,'',$field, $order);
        if(!$result){
            $result = array();
        }else{
            foreach($result as $k =>$v){
                unset($picture);
                $result[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
                if($v['picture'] != ''){
                    $pic = explode(',',$v['picture']);
                    $picture = array();
                    foreach($pic as $key =>$val){
                        $photo = $this ->searchPhoto($val);
                        $picture[$key]['pic'] = $photo?$photo:'';
                    }
                }
                $result[$k]['picture'] = $picture?$picture:array();
            }
        }

        apiResponse('1','',$result);
    }
}