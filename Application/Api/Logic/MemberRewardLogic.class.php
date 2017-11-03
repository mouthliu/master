<?php
namespace Api\Logic;
/**
 * Class MemberLogic
 * @package Api\Logic
 * 用户悬赏订单
 */
class MemberRewardLogic extends BaseLogic{
    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 悬赏订单列表
     * 标题搜索    title        可以为空
     * 免费围观    free_watch   可以为空或者为1
     * 悬赏类型    reward_id    可以为空
     * 排序方式    order        可以为空  1  最新  2  最热  为空时默认
     * 已围观      is_watch     1  已查看  可以为空
     * 分页参数    p
     */
    public function rewardList($request = array()){
        if($request['token']){
            $member = $this ->searchMember($request['token']);
        }
        //获取悬赏类型
        $where = array('status'=>array('neq',9));
        $field = 'id as reward_id, reward_name';
        $order = 'sort desc, create_time desc';
        $reward = $this ->easyMysql('Reward','4',$where,'',$field,$order);
        if(!$reward){
            $reward = array();
        }
        $row['raward_id'] = '0';
        $row['reward_name'] = '全部分类';
        array_unshift($reward,$row);

        $result['reward'] = $reward;
        unset($where);
        unset($field);
        unset($order);
        if($request['title']){
            $where['rorder.title'] = array('like','%'.$request['title'].'%');
        }
        if($request['free_watch']){
            $where['rorder.free_watch'] = 1;
        }
        if($request['reward_id']){
            $where['rorder.reward_id'] = $request['reward_id'];
        }

        if($request['is_watch'] == 1){
            $which['m_id'] = $member['id'];
            $which['pay_status'] = 1;
            $which['status'] = array('neq',9);
            $reward_id = $this ->easyMysql('Watch',4,$which,'','rorder_id','create_time asc');
            if(!$reward_id){
                $result['reward_order'] = array();
                apiResponse('1','',$result);
            }else{
                $reward_res = array();
                foreach($reward_id as $k => $v){
                    $reward_res[] = $v['rorder_id'];
                }
                $where['rorder.id'] = array('in',$reward_res);
            }
        }

        if($request['order']){
            switch($request['order']){
                case 1: $order = 'rorder.create_time desc'; break;
                case 2: $order = 'rorder.watch_man desc, rorder.sort desc, rorder.create_time desc'; break;
                default : $order = 'rorder.sort desc, rorder.create_time desc';
            }
        }else{
            $order = 'rorder.sort desc, rorder.create_time desc';
        }
        $where['rorder.pay_status'] = 1;
        $where['rorder.reward_time'] = array('egt',time());
        $where['rorder.status'] = array('neq',9);
        $field = 'rorder.id as rorder_id, rorder.m_id, rorder.title, rorder.create_time, rorder.reward_price, rorder.free_watch, rorder.is_anonymous, rorder.watch_man, reward.reward_name, member.nickname, member.head_pic';
        $reward_order = D('RewardOrder') ->selectRewardOrder($where, $field, $order, $request['p'],1);
        if(!$reward_order){
            $reward_order = array();
        }else{
            foreach($reward_order as $k => $v){
                if($v['is_anonymous'] == 2){
                    $reward_order[$k]['nickname'] = '';
                    $reward_order[$k]['head_pic'] = '';
                }else{
                    $head_pic = $this ->searchPhoto($v['head_pic']);
                    $reward_order[$k]['head_pic'] = $head_pic?$head_pic:C('API_URL').'/Uploads/Member/default.png';
                }

                if($v['m_id'] == $member['id']){
                    $reward_order[$k]['rorder_type'] = '1';
                }else{
                    $reward_order[$k]['rorder_type'] = '2';
                }

                $reward_order[$k]['create_time'] = $this ->format_time($v['create_time']);

                unset($where);
                unset($answer);
                $where['r_o_id'] = $v['rorder_id'];
                $where['is_adopt'] = 1;
                $where['status'] = array('neq',9);
                $answer = $this ->easyMysql('RorderAnswer',3,$where);
                if(!$answer){
                    unset($reward_order[$k]);
                }
            }
        }
        $reward_order = array_values($reward_order);
        $result['reward_order'] = $reward_order;

        apiResponse('1','',$result);
    }

    /**
     * 我的悬赏订单列表
     */
    public function myRewardList($request = array()){
        $member = $this ->searchMember($request['token']);
        $where = array('rorder.m_id'=>$member['id'], 'rorder.status'=>array('neq',9),'rorder.pay_status'=>1);
        $field = 'rorder.id as rorder_id, rorder.title, rorder.create_time, rorder.reward_price, rorder.free_watch, rorder.watch_man, rorder.is_anonymous, reward.reward_name, member.nickname, member.head_pic';
        $order = 'rorder.create_time desc';
        $result = D('RewardOrder') ->selectRewardOrder($where, $field, $order, $request['p'], 1);
        if(!$result){
            $result = array();
        }else{
            foreach($result as $k => $v){
                if($v['is_anonymous'] == 2){
                    $result[$k]['nickname'] = '';
                    $result[$k]['head_pic'] = '';
                }else{
                    $head_pic = $this ->searchPhoto($v['head_pic']);
                    $result[$k]['head_pic'] = $head_pic?$head_pic:C('API_URL').'/Uploads/Member/default.png';
                }
                $result[$k]['rorder_type'] = '1';
//                $result[$k]['create_time'] = date('Y-m-d',$v['create_time']);
                $result[$k]['create_time'] = $this ->format_time($v['create_time']);
            }
        }

        apiResponse('1','',$result);
    }

    /**
     * 我的悬赏订单详情
     */
    public function myRewardInfo($request = array()){
        //获取用户信息
        $member = $this ->searchMember($request['token']);
        //大概获取订单信息
        $where['rorder.id'] = $request['rorder_id'];
        $where['rorder.m_id'] = $member['id'];
        $where['rorder.status'] = array('neq',9);
        $where['rorder.pay_status'] = 1;
        $field = 'rorder.id as rorder_id, rorder.master_id, rorder.title, rorder.birthday,rorder.content, rorder.picture, reward.reward_name, rorder.reward_days, rorder.reward_price, rorder.free_watch, rorder.reward_time, rorder.is_anonymous, rorder.watch_man, member.nickname, member.head_pic, rorder.create_time';
        $rorder_info = D('RewardOrder') ->selectRewardOrder($where, $field, '', '', 2);
        if(!$rorder_info){
            apiResponse('0','订单信息有误');
        }
        //获取图片信息
        if(!empty($rorder_info['picture'])){
            $picture = explode(',',$rorder_info['picture']);
            if(!empty($picture)){
                $pic = array();
                foreach($picture as $k => $v){
                    $path = $this ->searchPhoto($v);
                    $pic[$k]['picture'] = $path;
                }
            }else{
                $pic = array();
            }
        }else{
            $pic = array();
        }
        $rorder_info['picture'] = $pic;
        if($rorder_info['is_anonymous'] == 1){
            $head_pic = $this ->searchPhoto($rorder_info['head_pic']);
            $rorder_info['head_pic'] = $head_pic?$head_pic:C('API_URL').'/Uploads/Member/default.png';
        }else{
            $rorder_info['head_pic'] = '';
            $rorder_info['nickname'] = '';
        }

        //获取大师信息
        if($rorder_info['master_id']){
            $master = explode(',',$rorder_info['master_id']);
            $master_info = array();
            foreach($master as $k => $v){
                unset($res);
                $res = $this ->easyMysql('Master','5',array('id'=>$v,'status'=>1),'','nickname');
                if($res){
                    $master_info[] = $res;
                }
            }
            $rorder_info['master_info'] = implode(',',$master_info);
        }else{
            $rorder_info['master_info'] = '未指定大师';
        }
        //获取剩余时间  和生辰八字
        $remain_time = ($rorder_info['reward_time'] - time())/86400;
        $rorder_info['remain_time'] = $remain_time > 0? floor($remain_time).'': '已结束';
//        $rorder_info['create_time'] = date('Y-m-d H:i:s',$rorder_info['create_time']);
        $rorder_info['create_time'] = $this ->format_time($rorder_info['create_time']);
        //生辰
        $birthday = explode("-",$rorder_info['birthday']);
        $month = $this->convertSolarToLunar($birthday[0],$birthday[1],$birthday[2]);
        $month = $month[0].'-'.$month[1].'-'.$month[2].'-'.$month[3];
        $rorder_info['horoscope'] = $month;

        //获取大师的答案们
        unset($where);
        unset($field);
        $where['answer.r_o_id'] = $request['rorder_id'];
        $where['answer.status'] = array('neq',9);
        $field = 'answer.id as answer_id, answer.content, answer.is_adopt, answer.create_time, master.nickname, master.head_pic';
        $order = 'answer.is_adopt, answer.create_time desc';
        $answer = D('RewardOrder') ->selectAnswer($where,$field,$order,1);

        if(!$answer){
            $answer = array();
        }else{
            foreach($answer as $k => $v){
                unset($head_pic);
                $head_pic = $this ->searchPhoto($v['head_pic']);
                $answer[$k]['head_pic'] = $head_pic?$head_pic:C('API_URL').'/Uploads/Master/default.png';
//                $answer[$k]['create_time'] = date('Y-m-d',$v['create_time']);
                $answer[$k]['create_time'] = $this ->format_time($v['create_time']);
            }
        }

        $rorder_info['answer'] = $answer;

        apiResponse('1','',$rorder_info);
    }

    /**
     * 悬赏订单详情
     */
    public function rewardInfo($request = array()){
        //获取用户信息
        $member = $this ->searchMember($request['token']);
        //大概获取订单信息
        $where['rorder.id'] = $request['rorder_id'];
//        $where['rorder.m_id'] = $member['id'];
        $where['rorder.status'] = array('neq',9);
        $where['rorder.pay_status'] = 1;
        $field = 'rorder.id as rorder_id, rorder.m_id, rorder.master_id, rorder.title, rorder.content, rorder.picture, reward.reward_name, rorder.reward_days, rorder.reward_price, rorder.free_watch, rorder.reward_time, rorder.is_anonymous, rorder.watch_man, member.nickname, member.head_pic, rorder.create_time';
        $rorder_info = D('RewardOrder') ->selectRewardOrder($where, $field, '', '', 2);
        if(!$rorder_info){
            apiResponse('0','订单信息有误');
        }

        if($rorder_info['is_anonymous'] == 1){
            $head_pic = $this ->searchPhoto($rorder_info['head_pic']);
            $rorder_info['head_pic'] = $head_pic?$head_pic:C('API_URL').'/Uploads/Member/default.png';
        }else{
            $rorder_info['head_pic'] = '';
            $rorder_info['nickname'] = '';
        }

        //获取图片信息
        if(!empty($rorder_info['picture'])){
            $picture = explode(',',$rorder_info['picture']);
            if(!empty($picture)){
                $pic = array();
                foreach($picture as $k => $v){
                    $path = $this ->searchPhoto($v);
                    $pic[$k]['picture'] = $path;
                }
            }else{
                $pic = array();
            }
        }else{
            $pic = array();
        }

        $rorder_info['picture'] = $pic;
        //获取剩余时间
        $remain_time = ($rorder_info['reward_time'] - time())/86400;
        $rorder_info['remain_time'] = $remain_time > 0 ? floor($remain_time).'' : '已结束';
//        $rorder_info['create_time'] = date('Y-m-d H:i:s', $rorder_info['create_time']);
        $rorder_info['create_time'] = $this ->format_time($rorder_info['create_time']);
        //看我是否围观
        unset($where);
        $where['rorder_id'] = $request['rorder_id'];
        $where['m_id'] = $member['id'];
        $where['pay_status'] = 1;
        $watch = $this ->easyMysql('Watch','3',$where);

        if($rorder_info['m_id'] != $member['id']){
            if(!$watch){
                $rorder_info['watchin'] = '2';
            }else{
                $rorder_info['watchin'] = '1';
            }
        }else{
            $rorder_info['watchin'] = '3';
        }
        //获取大师的答案们
        unset($where);
        unset($field);
        $where['answer.r_o_id'] = $request['rorder_id'];
        $where['answer.status'] = array('neq',9);
        $where['answer.is_adopt'] = 1;
        $field = 'answer.id as answer_id, answer.content, answer.is_adopt, answer.create_time, master.nickname, master.head_pic';

        $answer = D('RewardOrder') ->selectAnswer($where, $field, '', 2);

        if($answer){
            $head_pic = $this ->searchPhoto($answer['head_pic']);
            $answer['head_pic'] = $head_pic?$head_pic:C('API_URL').'/Uploads/Master/default.png';
//            $answer['create_time'] = date('Y-m-d',$answer['create_time']);
            $answer['create_time'] = $this ->format_time($answer['create_time']);
            $answer['status'] = '1';
            if($rorder_info['watchin'] != 1){
                $answer['content'] = '';
                $answer['watchin'] = '2';
            }else{
                $answer['watchin'] = '1';
            }
        }else{
            $answer['status'] = '2';
        }

        $rorder_info['answer'] = $answer;

        apiResponse('1','',$rorder_info);
    }

    /**
     * 采纳答案
     */
    public function adoptAnswer($request = array()){
        $member = $this ->searchMember($request['token']);
        $where['id'] = $request['rorder_id'];
        $where['m_id'] = $member['id'];
        $rorder = $this ->easyMysql('RewardOrder',3,$where);
        if(!$rorder){
            apiResponse('0','订单信息有误');
        }
        unset($where);
        $where['r_o_id'] = $request['rorder_id'];
        $where['id'] = $request['answer_id'];
        $where['status'] = array('neq',9);
        $res = $this ->easyMysql('RorderAnswer','3',$where);
        if(!$res){
            apiResponse('0','答案信息有误');
        }
        $data['is_adopt'] = 1;
        $data['update_time'] = time();
        $result = $this ->easyMysql('RorderAnswer','2',$where,$data);
        if(!$result){
            apiResponse('0','采纳失败');
        }
        $where['id'] = array('neq',$request['answer_id']);
        $data['is_adopt'] = 2;
        $result = $this ->easyMysql('RorderAnswer','2',$where,$data);

        $reward_offer = $this ->easyMysql('Config','5',array('id'=>93),'','value');
        $reward_price = ($rorder['reward_price']/100)*(100 - $reward_offer);
        $master_price = $this ->setType('Master',array('id'=>$res['master_id']),'balance',$reward_price,1);
        if(!$master_price){
            apiResponse('0','采纳金额有误');
        }
        $member_integral = $this ->setType('Member',array('id'=>$member['id']),'integral',$rorder['reward_price'],1);
        if(!$member_integral){
            apiResponse('0','用户添加积分有误');
        }

        //输入用户信息  商家信息  商家详情  商家明细
        $message_member = $this ->addMessage(1,$member['id'],2,$request['rorder_id'],'悬赏消息','您已成功采纳答案，感谢您的使用');
        if($member['push_message'] == 1){
            //极光推送
            //调用极光推送
            $con   = '您已成功采纳答案，感谢您的使用';
            $arr_j = $member['token'];
            $this->jPushToMember($arr_j, $request['rorder_id'], $con ,$con);
        }

        $message_master = $this ->addMessage(2,$res['master_id'],2,$request['rorder_id'],'悬赏消息','您的答案已被成功采纳，感谢您的使用');
        //极光推送
        //调用极光推送
        $master = $this ->easyMysql('Master',3,array('id'=>$res['master_id']));
        $con   = '您的答案已被成功采纳，感谢您的使用';
        $arr_j = $master['token'];
        $this->jPushToMaster($arr_j, $request['rorder_id'], $con ,$con);

        $detail = $this ->addDetail(2, $res['master_id'], 1, '悬赏订单', 1, $reward_price);
        $pay_log = $this ->addPayLog($member['id'], $res['master_id'], 3, '悬赏订单', $reward_price);

        apiResponse('1','采纳成功');
    }

    /**
     * 我要围观
     */
    public function gonnaWatch($request = array()){
        $member = $this ->searchMember($request['token']);
        $where = array('m_id'=>$member['id'],'rorder_id'=>$request['rorder_id']);
        $res = $this ->easyMysql('Watch',3,$where);
        if($res['pay_status'] == 1){
            apiResponse('0','您已围观该订单');
        }
        $where = array('id'=>$request['rorder_id'],'status'=>array('neq',9));
        $result = $this ->easyMysql('RewardOrder','3',$where);
        if(!$result){
            apiResponse('0','订单信息有误');
        }

        $order_sn = date('Ymd').rand(1000000,9999999);

        $data['rorder_id'] = $request['rorder_id'];
        $data['m_id']      = $member['id'];
        $data['order_sn']  = $order_sn;
        $data['create_time'] = time();
        if($result['free_watch'] == 1){
            $data['pay_type']  = 4;
            $data['pay_status'] = 1;
            $data['price']     = '0.00';
        }else{
            $data['pay_type']  = 0;
            $data['pay_status'] = 0;
            $data['price']     = '1.00';
        }
        $watch = $this ->easyMysql('Watch','1','',$data);
        if(!$watch){
            apiResponse('0','围观失败');
        }

        $res_data['free_watch'] = $result['free_watch'];
        $res_data['watch_id']   = $watch;
        $res_data['order_sn']   = $order_sn;
        $res_data['price']      = $data['price'];
        $res_data['balance']    = $member['balance'];

        if($res_data['free_watch'] == 1){
            //围观人数加1
            $rorder_watch = $this ->setType('RewardOrder',array('id'=>$request['rorder_id']),'watch_man',1,1);
            apiResponse('1','围观成功',$res_data);
        }else{
            apiResponse('1','',$res_data);
        }
    }

    /**
     * 提交悬赏
     * 用户标识   token
     * 大师ID     master_id
     * 上传多图   picture
     * 输入姓名   name
     * 性别       sex    1  男  2  女
     * 出生日期   birthday
     * 出生地     city_id
     * 标题       title
     * 问题详情   content
     * 悬赏类型   reward_id
     * 悬赏金额   reward_price
     * 围观类型   free_watch
     * 围观时间   reward_time
     * 匿名类型   is_anonymous
     */
    public function addReward($request = array()){
        $member = $this ->searchMember($request['token']);
        $order_sn             = date('Ymd',time()).rand(1000000,9999999);
        $data['order_sn']     = $order_sn;
        $data['m_id']         = $member['id'];
        if($request['master_id']){
            $data['master_id'] = $request['master_id'];
        }
        //发布图片不能为空w_r_pic0 w_r_pic1 w_r_pic2
        if($_FILES['picture']['name']){
            $res_pic = api('UploadPic/upload', array(array('save_path' => "Reward")));
            $picture = array();
            foreach ($res_pic as $k => $value) {
                if($value['key'] == 'picture'){
                    $picture[] = $value['id'];
                }
            }
            $data['picture'] = implode(',',$picture);
        }
        $data['name']         = $request['name'];
        $data['birthday']     = $request['birthday'];
        $data['city']         = $request['city_id'];
        $data['sex']          = $request['sex'];
        $data['title']        = $request['title'];
        $data['content']      = $request['content'];
        $data['reward_id']    = $request['reward_id'];
        $data['reward_price'] = $request['reward_price'];
        $data['free_watch']   = $request['free_watch'];
        $data['reward_days']  = $request['reward_time'];
        $data['reward_time']  = ($request['reward_time'] * 86400) + strtotime(date('Y-m-d',time())) + 86399;
        $data['is_anonymous'] = $request['is_anonymous'];
        $data['create_time']  = time();
        $result = $this ->easyMysql('RewardOrder','1','',$data);
        if(!$result){
            apiResponse('0','提交失败');
        }
        //输入用户信息  商家信息  商家详情  商家明细
        $message_member = $this ->addMessage(1,$member['id'],2,$result,'悬赏消息','您已成功发布悬赏信息，请立即支付');
        if($member['push_message'] == 1){
            //极光推送
            //调用极光推送
            $con   = '您已成功发布悬赏信息，请立即支付';
            $arr_j = $member['id'];
            $this->jPushToMember($arr_j, $result, $con ,$con);
        }

        $result_data['rorder_id'] = $result.'';
        $result_data['price']     = $request['reward_price'];
        $result_data['order_sn']  = $order_sn;
        $result_data['balance']   = $member['balance'];
        apiResponse('1','提交成功',$result_data);
    }

    /**
     * 悬赏类型
     */
    public function rewardType(){
        $where = array('status'=>array('neq',9));
        $field = 'id as r_t_id, reward_name';
        $order = 'sort desc, create_time desc';
        $reward_type = $this ->easyMysql('Reward','4',$where,'',$field,$order);
        if(!$reward_type){
            $reward_type = array();
        }
        apiResponse('1','',$reward_type);
    }

    /**
     * 大师列表
     * 大师昵称  nickname
     * 服务类别  service_id
     */
    public function masterList($request = array()){
        $where = array('status'=>array('neq',9));
        $field = 'id as service_id, title';
        $order = 'sort desc, create_time desc';
        $service = $this ->easyMysql('Service','4',$where,'',$field,$order);
        if(!$service){
            $service = array();
        }
        $result['service'] = $service;

        unset($where);
        if($request['nickname']){
            $where['master.nickname'] = array('like','%'.$request['nickname'].'%');
        }
        if($request['service_id']){
            $where['m_s.service_id'] = $request['service_id'];
        }
        $where['master.status'] = 1;
        $where['m_s.status'] = array('neq',9);
        $field = 'm_s.id as m_s_id, master.id as master_id, master.nickname, master.head_pic';
        $order = 'master.nickname asc, master.create_time desc';
        $master = D('Master') ->typeMaster($where, $field, $order);
        if(!$master){
            $master = array();
        }else{
            foreach($master as $k => $v){
                $head_pic = $this ->searchPhoto($v['head_pic']);
                $master[$k]['head_pic'] = $head_pic?$head_pic:C('API_URL').'/Uploads/Master/default.png';
            }
        }
        $result['master'] = $master;

        apiResponse('1','',$result);
    }

    /**
     * 服务支付页面
     */
    public function rewardPayPage($request = array()){
        $member = $this ->searchMember($request['token']);
        $result['m_id'] = $member['id'];
        $result['rorder_id'] = $request['order_id'];
        $result['order_sn'] = $request['order_sn'];
        $result['price'] = $request['price'];
        apiResponse('1','',$result);
    }

    /**
     * 围观支付页面
     */
    public function watchPayPage($request = array()){
        $member = $this ->searchMember($request['token']);
        $result['m_id']     = $member['id'];
        $result['watch_id'] = $request['watch_id'];
        $result['order_sn'] = $request['order_sn'];
        $result['price']    = $request['price'];
        apiResponse('1','',$result);
    }

    /**
     * 悬赏订单详情
     */
    public function rewardInfoPage($request = array()){
        $member = $this ->searchMember($request['token']);
        $where['rorder.id'] = $request['rorder_id'];
        $where['rorder.m_id'] = $member['id'];
        $where['rorder.status'] = array('neq',9);
        $field = 'rorder.id as rorder_id, rorder.name, rorder.birthday, region.region_name, rorder.title, rorder.content, rorder.picture, reward.reward_name, rorder.reward_price, rorder.free_watch, rorder.reward_days, is_anonymous, rorder.master_id, rorder.sex';
        $rorder = D('RewardOrder') ->selectRewardOrder($where, $field, '', '', 2, '');
        if(!$rorder){
            apiResponse('0','订单详情有误');
        }else{
            if($rorder['master_id'] != 0&&$rorder['master_id'] != ''){
                $master = $this ->easyMysql('Master',3,array('id'=>$rorder['master_id']),'','nickname');
            }
            $rorder['nickname'] = $master?$master['nickname']:'';
            if(!empty($rorder['picture'])){
                $picture = explode(',',$rorder['picture']);
                $pic = array();
                foreach($picture as $k => $v){
                    $photo = $this ->searchPhoto($v);
                    $pic[$k]['picture'] = $photo?$photo:'';
                    $pic[$k]['picture_id'] = $v;
                }
            }
            $rorder['picture'] = $pic?$pic:array();
        }

        apiResponse('1','',$rorder);
    }

    /**
     * 修改详情信息
     * 用户token     token
     * 悬赏订单id    rorder_id
     * 姓名          name
     * 性别          sex
     * 阳历生日      birthday
     * 出生地        city_id
     * 标题          title
     * 详情          content
     * 图片          picture
     * 悬赏类型      reward_id
     * 悬赏金额      reward_price
     * 免费围观      free_watch
     * 悬赏时间      reward_time
     * 匿名提问      is_anonymous
     * 指定大师      master_id
     */
    public function modifyRewardInfo($request = array()){

        $member = $this ->searchMember($request['token']);
        //看看有没有人围观
        $where['rorder_id'] = $request['rorder_id'];
        $where['pay_status'] = 1;
        $where['status']    = array('neq',9);
        $watch = $this ->easyMysql('Watch',3,$where);
        if($watch){
            apiResponse('0','这条信息已有人围观');
        }
        $answer = $this ->easyMysql('RorderAnswer',3,array('r_o_id'=>$request['rorder_id'],'status'=>array('neq',9)));
        if($answer){
            apiResponse('0','这条信息已有人回复');
        }
        //现在开始修改
        if($request['name']){
            $data['name'] = $request['name'];
        }
        if($request['birthday']){
            $data['birthday'] = $request['birthday'];
        }
        if($request['sex']){
            $data['sex'] = $request['sex'];
        }
        if($request['city_id']){
            $data['city'] = $request['city_id'];
        }
        if($request['title']){
            $data['title'] = $request['title'];
        }
        if($request['content']){
            $data['content'] = $request['content'];
        }
        if($request['reward_id']){
            $data['reward_id'] = $request['reward_id'];
        }
        if($request['reward_price']){
            $data['reward_price'] = $request['reward_price'];
        }
        if($request['free_watch']){
            $data['free_watch'] = $request['free_watch'];
        }
        if($request['reward_time']){
            $data['reward_days']  = $request['reward_time'];
            $data['reward_time']  = ($request['reward_time'] * 86400) + strtotime(date('Y-m-d',time())) + 86399;
        }
        if($request['is_anonymous']){
            $data['is_anonymous'] = $request['is_anonymous'];
        }
        if($request['master_id']){
            $data['master_id'] = $request['master_id'];
        }
        //发布图片不能为空w_r_pic0 w_r_pic1 w_r_pic2
        if($_FILES['picture']['name']){
            $res_pic = api('UploadPic/upload', array(array('save_path' => "Reward")));
            $picture = array();
            foreach ($res_pic as $k => $value) {

                if($value['key'] == 'picture'){
                    $picture[] = $value['id'];
                }
            }
//            $data['picture'] = implode(',',$picture);
        }
        if($request['picture_old']&&!empty($picture)){
            $data['picture'] = $request['picture_old'].','.implode(',',$picture);
        }elseif(empty($request['picture_old'])&&!empty($picture)){
            $data['picture'] = implode(',',$picture);
        }else{
            $data['picture'] = $request['picture_old'];
        }

        $data['update_time'] = time();
        unset($where);
        $where['id'] = $request['rorder_id'];
        $where['m_id'] = $member['id'];

        $result = $this ->easyMysql('RewardOrder',2,$where,$data);
        if(!$result){
            apiResponse('0','修改失败');
        }
        apiResponse('1','修改成功');
    }

    /**
     * 删除悬赏信息
     */
    public function deleteReward($request = array()){
        $member = $this ->searchMember($request['token']);
        $where['id'] = $request['rorder_id'];
        $where['m_id'] = $member['id'];
        $where['status'] = array('neq',9);
        $rorder = $this ->easyMysql('RewardOrder','3',$where);
        if(!$rorder){
            apiResponse('0','悬赏订单信息有误');
        }
        $data['status'] = 9;
        $data['update_time'] = time();
        $result = $this ->easyMysql('RewardOrder','2',$where, $data);
        if(!$result){
            apiResponse('0','删除信息失败');
        }
        apiResponse('1','删除成功');
    }
}