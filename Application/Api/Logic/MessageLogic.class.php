<?php
namespace Api\Logic;
/**
 * Class MessageLogic
 * @package Api\Logic
 * 信息模块
 */
class MessageLogic extends BaseLogic{

    /**
     * 消息首页
     */
    public function serviceMessage($request = array()){
        if(!$_POST['easemob_json']){
            apiResponse('1','',array());
        }
        $easemob_json = json_decode($_POST['easemob_json'], true);
        if(!$easemob_json){
            apiResponse('0','json有误');
        }

        $result = array();
        if($request['user_type'] == 1){
            foreach($easemob_json as $k => $v){
                $where = array('easemob_account'=>$v['easemob_account'],'status'=>array('neq',9));
                $field = 'id as master_id, nickname, head_pic, easemob_account';
                $res_data = $this ->easyMysql('Master','3',$where,'',$field);
                if(!$res_data){
                    continue;
                }
                $head_pic = $this ->searchPhoto($res_data['head_pic']);
                $res_data['head_pic'] = $head_pic?$head_pic:C('API_URL').'/Uploads/Master/default.png';
                $result[] = $res_data;
            }
        }else{
            foreach($easemob_json as $k => $v){
                $where = array('easemob_account'=>$v['easemob_account'],'status'=>array('neq',9));
                $field = 'id as master_id, nickname, head_pic, easemob_account';
                $res_data = $this ->easyMysql('Member','3',$where,'',$field);
                if(!$res_data){
                    continue;
                }
                $head_pic = $this ->searchPhoto($res_data['head_pic']);
                $res_data['head_pic'] = $head_pic?$head_pic:C('API_URL').'/Uploads/Member/default.png';
                $result[] = $res_data;
            }
        }

        apiResponse('1','',$result);
    }

    /**
     * 消息列表
     */
    public function messageList($request = array()){
        if($request['user_type'] == 1){
            $res = $this ->searchMember($request['token']);
        }else{
            $res = $this ->searchMaster($request['token']);
        }
        $where = array('user_type'=>$request['user_type'], 'user_id'=>$res['id'], 'type'=>$request['type'], 'status'=>array('neq',9));
        $field = 'id as message_id, headline, content, create_time';
        $order = 'create_time desc';
        $result = $this ->easyMysql('Message', '4', $where, '', $field, $order, $request['p']);
        if(!$result){
            $result = array();
        }else{
            foreach($result as $k => $v){
                $result[$k]['create_time'] = date('Y-m-d',$v['create_time']);
            }
        }

        apiResponse('1','',$result);
    }

    /**
     * 消息详情
     */
    public function messageInfo($request = array()){
        $where = array('id'=>$request['message_id'],'status'=>array('neq',9));
        $field = 'id as message_id, headline, content, create_time';
        $result = $this ->easyMysql('Message', '3', $where, '', $field);
        if(!$result){
            apiResponse('0','信息详情有误');
        }
        preg_match_all('/src=\"\/?(.*?)\"/',$result['content'],$match);
        foreach($match[1] as $key => $src){
            if(!strpos($src,'://')){
                $result['content'] = str_replace('/'.$src,C('API_URL')."/".$src."\" width=100%",$result['content']);
            }
        }
        $result['create_time'] = date('Y-m-d',$result['create_time']);
        apiResponse('1','',$result);
    }

    /**
     * 随缘红包
     */
    public function revelPrice($request = array()){
        $member = $this ->searchMember($request['token']);
        $master = $this ->easyMysql('Master','3',array('id'=>$request['master_id'],'status'=>array('neq',9)));
        if($master['max_red'] == 0||$master['max_red'] == '0.00'){
            apiResponse('0','该大师并未设置随缘金额');
        }
        $order_sn = date('Ymd',time()).rand('1000000','9999999');
        $price = rand($master['min_red'] * 100 , $master['max_red'] * 100);
        $data['m_id']  = $member['id'];
        $data['title'] = '随缘金额';
        $data['type']  = '1';
        $data['price'] = $price/100;
        $data['master_id'] = $master['id'];
        $data['order_sn'] = $order_sn;
        $data['create_time'] = time();
        $res = $this ->easyMysql('RedPackage','1','',$data);
        if(!$res){
            apiResponse('0','发布红包失败');
        }

        $result['red_id'] = $res;
        $result['order_sn'] = $order_sn;
        $result['price']  = $data['price'].'';
        apiResponse('1','发布红包成功',$result);
    }

    /**
     * 大师打开红包
     */
    public function openPrice($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where  = array('id'=>$request['red_id'], 'pay_status'=>1, 'user_id'=>$master['id'], 'status'=>array('neq',9));
        $res    = $this ->easyMysql('RedPackage','3',$where);
        if(!$res){
            apiResponse('0','红包信息有误');
        }
        if($res['status'] == 2){
            apiResponse('0','您已领取该红包');
        }

        $data['status'] = 2;
        $data['update_time'] = time();
        $result = $this ->easyMysql('RedPackage','2',$where,$data);
        if(!$result){
            apiResponse('0','领取红包失败','1111');
        }

        $where  = array('id'=>$master['id']);
        $master_info = $this ->setType('Master', $where, 'balance', $res['price'], 1);
        if(!$master_info){
            apiResponse('0','领取红包失败');
        }
        $detail = $this ->addDetail('2',$master['id'],'1','领取红包','1',$res['price']);
        $pay_log = $this ->addPayLog($res['m_id'],$master['id'],4,'领取红包',$res['price']);

        apiResponse('1','领取红包成功');
    }

    /**
     * 摇卦卜卦
     */
    public function fortuneTelling(){
        $result = array();

        for($i = 0; $i<18; $i++){
            $result[$i]['num'] = rand(1,2).'';
        }
        if(empty($result)){
            apiResponse('0','卜卦失败');
        }

        apiResponse('1','卜卦成功',$result);
    }

    /**
     * 支付红包页面
     */
    public function redPayPage($request = array()){
        $member = $this ->searchMember($request['token']);
        $where  = array('id'=>$request['red_id'], 'pay_status'=>0, 'status'=>0);
        $red_package = $this ->easyMysql('RedPackage','3',$where);
        if(!$red_package){
            apiResponse('0','红包信息有误');
        }
        $where  = array('id'=>$red_package['master_id']);
        $master = $this ->easyMysql('Master','3',$where);
        if(!$master){
            apiResponse('0','大师信息有误');
        }
        $result['red_id']   = $request['red_id'];
        $result['order_sn'] = $request['order_sn'];
        $result['nickname'] = $master['nickname'];
        $head_pic = $this ->searchPhoto($master['head_pic']);
        $result['price']    = $request['price'];
        $result['head_pic'] = $head_pic?$head_pic:C('API_URL').'Uploads/Master/default.png';
        $result['balance']  = $member['balance'];

        apiResponse('1','',$result);
    }
}