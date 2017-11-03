<?php
namespace Api\Logic;
/**
 * Class MemberLogic
 * @package Api\Logic
 * 大师悬赏订单
 */
class MasterRewardLogic extends BaseLogic{
    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 正在悬赏订单
     */
    public function rewardOrderList($request = array()){
        if($request['token']){
            $master = $this ->searchMaster($request['token']);
        }
        //获取悬赏订单类型
        $where = array('status'=>array('neq',9));
        $field = 'id as reward_id, reward_name';
        $order = 'sort desc, create_time desc';
        $reward = $this ->easyMysql('Reward','4',$where,'',$field,$order);
        if(!$reward){
            $reward = array();
        }
        $result['reward'] = $reward;
        //如果悬赏类型存在
        unset($where);
        if($request['reward_id']){
            $where['rorder.reward_id'] = $request['reward_id'];
        }
        //type == 1  指定大师自己  2  等待自己回答
        if($request['type'] == 1){
            if(!$master){
                $result['rorder'] = array();
                apiResponse('1','',$result);
            }else{
//                $where['_string'] = " ( rorder.master_id = ".$master['id'].") OR ( rorder.master_id like '%,".$master['id'].",%') OR ( rorder.master_id like '%,".$master['id']."') OR ( rorder.master_id like '".$master['id'].",%' )";
                $where['rorder.master_id'] = $master['id'];
            }
        }else{
            $where['rorder.master_id'] = 0;
        }
        $where['rorder.reward_time'] = array('egt',time());
        $where['rorder.pay_status']  = 1;
        $where['rorder.status'] = array('neq',9);
        $field = 'rorder.id as rorder_id, rorder.master_id, rorder.title, rorder.reward_price, rorder.create_time, rorder.is_anonymous, reward.reward_name, rorder.watch_man, member.nickname, member.head_pic';
        $order = 'rorder.create_time desc';
        $rorder = D('RewardOrder') ->selectRewardOrder($where , $field , $order , $request['p'], 1);
        unset($where);
        if(!$rorder){
            $rorder = array();
        }else{
            foreach($rorder as $k =>$v){
                unset($head_pic);
//                $rorder[$k]['create_time'] = date('Y-m-d',$v['create_time']);
                $rorder[$k]['create_time'] = $this ->format_time($v['create_time']);
                if($v['is_anonymous'] == 1){
                    $head_pic = $this ->searchPhoto($v['head_pic']);
                    $rorder[$k]['head_pic'] = $head_pic?$head_pic:C('API_URL').'Uploads/Member/default.png';
                }else{
                    $rorder[$k]['head_pic'] = '';
                    $rorder[$k]['nickname'] = '';
                }
                if($master){
                    $where = array('master_id'=>$master['id'],'r_o_id'=>$v['rorder_id'],'status'=>array('neq',9));
                    $answer = $this ->easyMysql('RorderAnswer',3,$where);
                    if($answer){
                        unset($rorder[$k]);
                    }
                }
            }
        }
        $result['rorder'] = array_values($rorder);

        apiResponse('1','',$result);
    }

    /**
     * 已完成订单
     */
    public function completeOrderList($request = array()){
        $master = $this ->searchMaster($request['token']);
        //获取悬赏订单类型
        $where = array('status'=>array('neq',9));
        $field = 'id as reward_id, reward_name';
        $order = 'sort desc, create_time desc';
        $reward = $this ->easyMysql('Reward','4',$where,'',$field,$order);
        if(!$reward){
            $reward = array();
        }
        $result['reward'] = $reward;
        //悬赏类型不为空
        unset($where);
        if($request['reward_id']){
            $where['rorder.reward_id'] = $request['reward_id'];
        }
//        $where['rorder.reward_time'] = array('lt',time());
        //type状态  1  大师已回答  2  大师被采纳
        if($request['type'] == 1){
            $where['answer.master_id'] = $master['id'];
        }else{
            $where['answer.master_id'] = $master['id'];
            $where['answer.is_adopt']  = 1;
        }
//        $where['rorder.reward_time']   = array('lt',time());
        $where['rorder.status'] = array('neq',9);
        $field = 'rorder.id as rorder_id, rorder.title, rorder.reward_price, rorder.create_time, rorder.is_anonymous, reward.reward_name, rorder.watch_man, member.nickname, member.head_pic';
        $order = 'rorder.create_time desc';

        $rorder = D('RewardOrder') ->selectAnswerList($where, $field, $order, $request['p']);
        if(!$rorder){
            $rorder = array();
        }else{
            foreach($rorder as $k =>$v){
                unset($head_pic);
//                $rorder[$k]['create_time'] = date('Y-m-d',$v['create_time']);
                $rorder[$k]['create_time'] = $this ->format_time($v['create_time']);

                if($v['is_anonymous'] == 1){
                    $head_pic = $this ->searchPhoto($v['head_pic']);
                    $rorder[$k]['head_pic'] = $head_pic?$head_pic:C('API_URL').'Uploads/Member/default.png';
                }else{
                    $rorder[$k]['head_pic'] = '';
                    $rorder[$k]['nickname'] = '';
                }
            }
        }

        $result['rorder'] = $rorder;

        apiResponse('1','',$result);
    }

    /**
     * 订单详情
     */
    public function rewardOrderInfo($request = array()){
        if($request['type'] == 2){
            $master = $this ->searchMaster($request['token']);
        }
        if($request['type'] == 1){
            $where = array('rorder.id'=>$request['rorder_id'],'rorder.status'=>array('neq',9),'rorder.reward_time'=>array('gt',time()));
            $field = 'rorder.id as rorder_id, rorder.master_id, rorder.birthday,rorder.title, rorder.content, rorder.picture, rorder.reward_price, rorder.free_watch, rorder.reward_days, rorder.reward_time, rorder.watch_man, rorder.is_anonymous, rorder.create_time, reward.reward_name, member.nickname, member.head_pic';
            $result = D('RewardOrder') ->selectRewardOrder($where, $field , '', '', 2);
        }else{
            $where = array('rorder.id'=>$request['rorder_id'],'rorder.status'=>array('neq',9),'answer.master_id'=>$master['id']);
            $field = 'rorder.id as rorder_id, rorder.master_id, rorder.birthday,rorder.title, rorder.content, rorder.picture, rorder.reward_price, rorder.free_watch, rorder.reward_days, rorder.reward_time, rorder.watch_man, rorder.is_anonymous, rorder.create_time, reward.reward_name, member.nickname, member.head_pic, master.nickname as mas_nickname, master.head_pic as mas_head_pic, answer.id as answer_id, answer.is_adopt, answer.content as answer_content, answer.create_time as answer_create_time';
            $result = D('RewardOrder') ->selectAnswerList($where , $field , '', '', '', 1);
        }

        if(!$result){
            apiResponse('0','悬赏订单详情有误');
        }
//      master.head_pic as mas_head_pic';
        if($result['master_id'] != ''&& $result['master_id'] != 0){
            $result['master_nickname'] = $this ->easyMysql('Master','5',array('id'=>$result['master_id']),'','nickname');
        }else{
            $result['master_nickname'] = '';
        }
        if($result['is_anonymous'] == 1){
            $head_pic = $this ->searchPhoto($result['head_pic']);
            $result['head_pic'] = $head_pic?$head_pic:C('API_URL').'/Uploads/Member/default.png';
        }else{
            $result['nickname'] = '';
            $result['head_pic'] = '';
        }
        //2017年09月20日17:20:14
        //获取创建时间和生辰八字
        $result['create_time'] = $this ->format_time($result['create_time']);
        $birthday = explode("-",$result['birthday']);
        $month = $this->convertSolarToLunar($birthday[0],$birthday[1],$birthday[2]);
        $month = $month[0].'-'.$month[1].'-'.$month[2].'-'.$month[3];
        $result['horoscope'] = $month;
        $surplus_time = ($result['reward_time'] - time())/86400;
        if($surplus_time > 0){
            $result['surplus_time'] = floor($surplus_time).'';
        }else{
            $result['surplus_time'] = '已结束';
        }
        $pic = explode(',',$result['picture']);
        $picture = array();
        foreach($pic as $k =>$v){
            unset($photo);
            $photo = $this ->searchPhoto($v);
            if($photo){
                $picture[$k]['pic'] = $photo;
            }
        }
        $result['picture'] = $picture;
        if($request['type'] == 2){
            $mas_head_pic = $this ->searchPhoto($result['mas_head_pic']);
            $result['mas_head_pic'] = $mas_head_pic?$mas_head_pic:C('API_URL').'/Uploads/Master/default.png';
            $result['answer_create_time'] = $this ->format_time($result['answer_create_time']);
        }

        apiResponse('1','',$result);
    }

    /**
     * 大师提交悬赏答案
     */
    public function rewardAnswer($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where  = array('r_o_id'=>$request['rorder_id'],'status'=>array('neq',9),'is_adopt'=>1);
        $answer = $this ->easyMysql('RorderAnswer', 3, $where);

        $where = array('id'=>$request['rorder_id'],'status'=>array('neq',9),'pay_status'=>1);
        $rorder = $this ->easyMysql('RewardOrder', 3, $where);
        if(!$rorder){
            apiResponse('0','悬赏订单信息有误');
        }

        $where  = array('master_id'=>$master['id'],'r_o_id'=>$request['rorder_id'],'status'=>array('neq',9));
        $res    = $this ->easyMysql('RorderAnswer', 3, $where);
        if($res){
            apiResponse('0','您已回答这个悬赏');
        }

        $data['r_o_id']    = $request['rorder_id'];
        $data['master_id'] = $master['id'];
        $data['content']   = $request['content'];
        if(!$answer){
            $data['is_adopt']  = 3;
        }else{
            $data['is_adopt']  = 2;
        }
        $data['create_time'] = time();
        //上传图片可以为空
        if(!empty($_FILES['picture']['name'])){
            $res = api('UploadPic/upload', array(array('save_path' => 'Reward')));
            $picture = array();
            foreach ($res as $value) {
                $picture[] = $value['id'];
            }
            $data['picture'] = implode(',',$picture);
        }
        $result = $this ->easyMysql('RorderAnswer','1','',$data);
        if(!$result){
            apiResponse('0','回复失败');
        }

        //输入用户信息  商家信息  商家详情  商家明细
        $message_member = $this ->addMessage(1,$rorder['m_id'],2,$request['rorder_id'],'悬赏消息','您的悬赏订单已有大师回复，请查看');
        $member = $this ->easyMysql('Member',3,array('id'=>$rorder['m_id']));
        if($member['push_message'] == 1){
            //极光推送
            //调用极光推送
            $con   = '您的悬赏订单已有大师回复，请查看';
            $arr_j = $member['token'];
            $this->jPushToMember($arr_j, $request['rorder_id'], $con ,$con);
        }

        $message_master = $this ->addMessage(2,$rorder['master_id'],2,$request['rorder_id'],'悬赏消息','您的答案已成功提交，感谢您的参与');
        //极光推送
        //调用极光推送
        $con   = '您的答案已成功提交，感谢您的参与';
        $arr_j = $master['token'];
        $this->jPushToMaster($arr_j, $request['rorder_id'], $con ,$con);

        apiResponse('1','回复成功');
    }
}