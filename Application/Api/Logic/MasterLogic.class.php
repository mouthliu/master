<?php
namespace Api\Logic;
/**
 * Class MemberLogic
 * @package Api\Logic
 * 大师模块
 */
class MasterLogic extends BaseLogic{
    /*
    * 验证验证码
    */
    public function _checkVerify($account,$verify,$type){
        $where['way']  = $account;
        $where['vc']   = $verify;
        $where['type'] = $type;
        //检查验证码是否错误
        $sms_info = M('Sms')->where($where)->find();
        if(empty($sms_info)){
            apiResponse('0','验证码错误');
        }

        //检查验证码是否过期
        if($sms_info['expire_time']<time()){
            apiResponse('0','对不起,您的验证码已过期');
        }
    }

    /*
    * 大师中心
    * 用户ID   账号token
    */
    public function  masterCenter($request = array()){
        $master = $this ->searchMaster($request['token']);
        $result_data['token'] = $master['token'];
        $result_data['nickname'] = $master['nickname'];
        if($master['passwd'] != ''){
            $result_data['passwd'] = '1';
        }else{
            $result_data['passwd'] = '2';
        }
        $result_data['easemob_account'] = $master['easemob_account'];
        $result_data['easemob_password'] = $master['easemob_password'];
        $path = $this ->easyMysql('File','5',array('id'=>$master['head_pic']),'','path');
        $result_data['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Master/default.png';
        $result_data['balance'] = $master['balance'];
        $result_data['auth_status'] = $master['auth_status'];
        $result_data['score'] = $master['score'];
        $result_data['sex'] = $master['sex'];
        $result_data['introduction'] = $master['introduction'];
        if($master['city'] == 0){
            $result_data['city_name'] = '';
        }else{
            $result_data['city_name'] = $this ->easyMysql('Region','5',array('id'=>$master['city']),'','region_name');
        }
        /*******一道华丽的分割线*******/
        if($master['social_id'] == 0){
            $result_data['social_status'] = '2';
            $result_data['social_name'] = '';
            $result_data['social_id'] = '0';
        }else{
            $result_data['social_id'] = $master['social_id'].'';
            $result_data['social_status'] = '1';
            $result_data['social_name'] = $this ->getFieldSth('Social',array('id'=>$master['social_id']),'social_name');
        }
        if($master['field_id'] != ''){
            $result_data['field'] = $this ->findFields($master['field_id']);
        }else{
            $result_data['field'] = array();
        }
        //总订单数  今日收益  今日订单  新订单  未完成订单  悬赏订单
        //获取总订单数
        unset($where);
        $where['master_id'] = $master['id'];
        $where['status']    = array('neq',10);
        $service_order = $this ->easyMysql('ServiceOrder',6,$where);
        $order = $this ->easyMysql('Order',6,$where);
        $result_data['total_order'] = ($service_order + $order).'';

        //获取今日收益
        unset($where);
        $start_time = strtotime(date('Y-m-d',time()));
        $where['master_id'] = $master['id'];
        $where['create_time'] = array('egt',$start_time);
        $today_balance = M('PayLog') ->where($where) ->field('COUNT(price) as total_price') ->find();
        $result_data['today_balance'] = $today_balance?$today_balance['total_price'].'':'0.00';

        //获取今日订单
        unset($where);
        $where['master_id'] = $master['id'];
        $where['status']    = array('neq',10);
        $where['create_time'] = array('egt',$start_time);
        $service_order = $this ->easyMysql('ServiceOrder',6,$where);
        $order = $this ->easyMysql('Order',6,$where);
        $result_data['today_order'] = ($service_order + $order).'';

        //获取新订单
        unset($where);
        $where['master_id'] = $master['id'];
        $where['status']    = array('neq',10);
        $where['s_order_status'] = 1;
        $where['pay_status'] = 1;
        $service_order = $this ->easyMysql('ServiceOrder',6,$where);
        $result_data['service_order'] = $service_order?$service_order.'':'0';
        unset($where);
        $where['master_id'] = $master['id'];
        $where['status']    = array('neq',10);
        $where['order_status'] = 1;
        $where['pay_status'] = 1;
        $order = $this ->easyMysql('Order',6,$where);
        $result_data['order'] = $order?$order.'':'0';
        $result_data['new_order']   = ($service_order + $order).'';

        //获取未完成订单
        unset($where);
        $where['master_id'] = $master['id'];
        $where['s_order_status'] = array('not in',array(0,4,5,7,8));
        $where['status']    = array('neq',10);
        $service_order = $this ->easyMysql('ServiceOrder',6,$where);
        unset($where);
        $where['master_id'] = $master['id'];
        $where['status']    = array('neq',10);
        $where['order_status'] = array('not in',array(0,4,5,7,8));
        $order = $this ->easyMysql('Order',6,$where);
        $result_data['unfinish_order'] = ($service_order + $order).'';

        //获取该大师的所有悬赏订单
        unset($where);
        $map['master_id'] = $master['id'];
        $map['master_id'] = 0;
        $map['_logic'] = 'OR';
        //$where['_complex'] = $map;
        //$where['reward_time'] = array('lt',time());
        //zhousl  查询已完成的被采纳的订单数量
//        $where['rorder.status'] = array('neq',9);
//        $where['answer.master_id'] = $master['id'];
//        $where['answer.is_adopt']  = 1;
//        $field = 'rorder.id as rorder_id, rorder.title, rorder.reward_price, rorder.create_time, rorder.is_anonymous, reward.reward_name, rorder.watch_man, member.nickname, member.head_pic';
//        $order = 'rorder.create_time desc';

//        $reward_order = D('RewardOrder') ->selectAnswerList($where, $field, $order, '1');
        //$reward_order = $this ->easyMysql('RewardOrder',6,$where);
//        $reward_order=count($reward_order);
        //$reward_order = $this ->easyMysql('RewardOrder',6,$where);

        $where['master_id'] = $master['id'];
        $where['reward_time'] = array('lt',time());
        $reward_order = $this ->easyMysql('RewardOrder',6,$where);

        $result_data['reward_order'] = $reward_order?$reward_order.'':'0';

        $result_data['service_line'] = $this ->easyMysql('Config','5',array('id'=>55),'','value');

        apiResponse('1','',$result_data);
    }

    /*
    * 大师余额
    * 用户ID   账号token
    */
    public function  masterbalance($request = array()){
        $master = $this ->searchMaster($request['token']);
        $result['balance'] = $master['balance'];
        apiResponse('1','',$result);
    }

    /*
    * 大师提现页
    * 用户ID   账号token
    */
    public function  masterWithdrawPage($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where['bank.user_id'] = $master['id'];
        $where['bank.user_type'] = 2;
        $where['bank.status'] = array('neq',9);
        $field = 'bank.id as bank_id, bank.bank_number, support_bank.bank_name';
        $bank = D('Member') ->selectBank($where,$field);
        if(!$bank){
            $bank['bank_id'] = '';
            $bank['bank_number'] = '';
            $bank['bank_name'] = '';
        }else{
            $bank_number = substr($bank['bank_number'],-4,4);
            $bank['bank_number'] = '**** **** **** '.$bank_number;
        }
        $result['balance'] = $master['balance'];
        $result['bank'] = $bank;

        apiResponse('1','',$result);
    }

    /*
    * 大师提现
    * 用户ID   账号token
    */
    public function  masterWithdraw($request = array()){
        $master = $this ->searchMaster($request['token']);
        if($master['balance'] < $request['price']){
            apiResponse('0','您的余额不足');
        }
        if($request['type'] == 1){
            $bank = $this ->easyMysql('Bank','3',array('id'=>$request['bank_id'],'status'=>array('neq',9),'user_type'=>2,'user_id'=>$master['id']));
            if(!$bank){
                apiResponse('0','银行卡信息有误');
            }
        }

        $data['user_type'] = 2;
        $data['user_id']   = $master['id'];
        $data['bank_id']   = $request['bank_id']?$request['bank_id']:'';
        $data['price']     = $request['price'];
        $data['create_time'] = time();
        $data['type']        = $request['type'];
        $data['alipay_account'] = $request['alipay_account']?$request['alipay_account']:'';
        $data['alipay_name'] = $request['alipay_name']?$request['alipay_name']:'';
        $withdraw = M('Withdraw') ->add($data);
        if(!$withdraw){
            apiResponse('0','提现失败');
        }

        $master_info = M('Master') ->where(array('id'=>$master['id'])) ->setDec('balance',$request['price']);
        if(!$master_info){
            apiResponse('0','提现失败');
        }
        $detail = $this ->addDetail('2',$master['id'],'2','提现','2',$request['price']);

        apiResponse('1','提现成功');
    }

    /*
    * 大师明细
    * 用户ID   账号token
    */
    public function  masterDetail($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where['user_type'] = 2;
        $where['user_id']   = $master['id'];
        $where['status']    = array('neq',9);
        $detail = $this ->easyMysql('Detail','4',$where,'','','create_time desc',$request['p']);
        if(!$detail){
            if($request['p']==1){
                apiResponse('0','暂无账单明细');
            }else{
                apiResponse('0','无更多数据');
            }
        }
        foreach($detail as $k => $v){
            $detail[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
        }

        apiResponse('1','',$detail);
    }

    /*
    * 服务列表
    * 用户ID   账号token
    */
    public function  serviceManage($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where['status'] = array('neq',9);
        $where['master_id'] = $master['id'];
        $where['status'] = array('neq',9);
        $service = $this ->easyMysql('MasterService','4',$where,'','id as m_s_id, service_id');
        $master_service = array();
        foreach($service as $k => $v){
            $master_service[] = $v['service_id'];
        }
        $field = 'id as service_id, title, content, price, picture, create_time';
        $order = 'create_time desc';
        $service_list = $this ->easyMysql('Service','4',array('status'=>array('neq',9)),'',$field,$order);
        foreach($service_list as $k => $v){
            unset($photo);
            if(in_array($v['service_id'],$master_service)){
                $service_list[$k]['choose'] = '1';
                $which['service_id'] = $v['service_id'];
                $which['master_id']  = $master['id'];
                $which['status']     = array('neq',9);
                $price = $this ->easyMysql('MasterService','3',$which,'','price');

                $service_list[$k]['price'] = $price?$price['price']:$v['price'];
            }else{
                $service_list[$k]['choose'] = '2';
            }
            $photo = $this ->searchPhoto($v['picture']);
            $service_list[$k]['picture'] = $photo?$photo:'';

            $choose[$k] = $service_list[$k]['choose'];
            $create_time[$k] = $v['create_time'];
        }

        array_multisort($choose, SORT_ASC, $create_time, SORT_DESC, $service_list);
        apiResponse('1','',$service_list);
    }

    /*
    * 编辑服务列表
    * 用户ID   账号token
    */
    public function serviceModify($request = array()){
        $master = $this ->searchMaster($request['token']);
        if(empty($_POST['service_json'])){
            apiResponse('1','操作成功');
        }
        $service_json = json_decode($_POST['service_json'],true);
        if(!$service_json){
            apiResponse('0','JSON有误');
        }

        $where['master_id'] = $master['id'];
        $data['status'] = 9;
        $result = $this ->easyMysql('MasterService','2',$where,$data);

        unset($data);

        foreach($service_json as $k => $v){
            $where['service_id'] = $v['service_id'];

            $res = $this ->easyMysql('MasterService','3',$where);
            if($res){
                $data['status'] = 1;
                $data['price'] = $v['price'];
                $data['update_time'] = time();
                $result_data = $this ->easyMysql('MasterService','2',$where,$data);
            }else{
                $data['master_id'] = $master['id'];
                $data['service_id'] = $v['service_id'];
                $data['price']     = $v['price'];
                $data['create_time'] = time();
                $result_data = $this ->easyMysql('MasterService','1','',$data);
            }
        }

        apiResponse('1','操作成功');
    }

    /*
    * 大师资料
    * 用户ID   账号token
    */
    public function masterInfo($request = array()){
        $master = $this ->searchMaster($request['token']);
        $path = $this ->easyMysql('File','5',array('id'=>$master['head_pic']),'','path');

        $result['nickname'] = $master['nickname'];
        $result['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Master/default.png';
        $result['sex']      = $master['sex'];
        $city = $this ->easyMysql('Region','5',array('id'=>$master['city']),'','region_name');
        $province = $this ->easyMysql('Region','5',array('id'=>$master['province']),'','region_name');
        $area = $this ->easyMysql('Region','5',array('id'=>$master['area']),'','region_name');
        $result['city_name'] = $province.$city.$area;
        $result['introduction'] = $master['introduction'];

        apiResponse('1','',$result);
    }

    /*
    * 修改大师资料
    * 大师标识    token
     * 大师昵称   nickname
     * 大师介绍   introduction
     * 城市ID     city_id
     * 性别       sex
     * 擅长领域   field
     * 头像       head_pic
    */
    public function masterModify($request = array()){
        $master = $this ->searchMaster($request['token']);
        if($request['nickname']){
            $data['nickname'] = $request['nickname'];
        }
        if($request['introduction']){
            $data['introduction'] = $request['introduction'];
        }
        if($request['city_id']){
            $data['city'] = $request['city_id'];
        }
        if($request['sex']){
            $data['sex'] = $request['sex'];
        }
        if($request['field']){
            $data['field_id'] = $request['field'];
        }
        if($request['province_id']){
            $data['province'] = $request['province_id'];
        }
        if($request['area_id']){
            $data['area'] = $request['area_id'];
        }
        //上传图片可以为空
        if(!empty($_FILES['head_pic']['name'])){
            $res = api('UploadPic/upload', array(array('save_path' => 'Master')));
            foreach ($res as $value) {
                $head_pic = $value['id'];
                $data['head_pic'] = $head_pic;
            }
        }
        $where['id'] = $master['id'];
        $data['update_time'] = time();
        $result = $this ->easyMysql('Master','2',$where, $data);
        if(!$result){
            apiResponse('0','修改个人资料失败');
        }
        //修改个人资料成功后返回头像
        unset($data);
        $last_head_pic = M('Master')->where(array('token'=>$request['token']))->getField('head_pic');
        if($last_head_pic){
            $path = M('File')->where(array('id'=>$last_head_pic))->getField('path');
            $data['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Master/default.png';
        }else{
            $data['head_pic'] = C('API_URL').'/Uploads/Member/default.png';
        }
        apiResponse('1','修改个人资料成功',$data);
    }

    /*
    * 擅长领域列表
    * 用户ID   账号token
    */
    public function fieldList($request = array()){
        $master = $this ->searchMaster($request['token']);
        $field = explode(',',$master['field_id']);

        $field_list = $this ->easyMysql('Field','4',array('status'=>array('neq',9)),'','id as field_id, field_name','sort desc, create_time desc');

        if(!$field_list){
            $field_list = array();
        }else{
            foreach($field_list as $k => $v){
                if(in_array($v['field_id'],$field)){
                    $field_list[$k]['choose'] = '1';
                }else{
                    $field_list[$k]['choose'] = '2';
                }
            }
        }
        apiResponse('1','',$field_list);
    }

    /*
    * 大师认证
    * 用户ID   账号token
    */
    public function Authentication($request = array()){
        $master = $this ->searchMaster($request['token']);
        $this ->_checkVerify($request['phone'],$request['verify'],'auth');
        if($_FILES['front_idcard']['name'] || $_FILES['back_idcard']['name'] || $_FILES['shop_pic']['name'] || $_FILES['hand_idcard']['name'] ){
            $res_pic = api('UploadPic/upload', array(array('save_path' => "Master")));
            foreach ($res_pic as $k => $value) {
                if($value['key'] == 'shop_pic'){
                    $data['shop_pic'] = $value['id'];
                }
                if($value['key'] == 'front_idcard'){
                    $data['front_idcard'] = $value['id'];
                }
                if($value['key'] == 'back_idcard'){
                    $data['back_idcard'] = $value['id'];
                }
                if($value['key'] == 'hand_idcard'){
                    $data['hand_idcard'] = $value['id'];
                }
            }
        }
        $data['name']   = $request['name'];
        $data['idcard'] = $request['idcard'];
        $data['phone']  = $request['phone'];
        $data['auth_status'] = 2;
        $where['id'] = $master['id'];
        $result = $this ->easyMysql('Master','2',$where,$data);
        if(!$result){
            apiResponse('0','提交审核失败');
        }
        apiResponse('1','提交审核成功');
    }

    /*
    * 账户明细
    * 用户token   账号token
    */
    public function payLog($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where['master_id'] = $master['id'];
        if($request['time_type'] == 1){
            $where['date'] = $request['date']?$request['date']:date('Y-m');
        }else{
            $start_time = strtotime(date('Y-m-d',time()));
            $where['create_time'] = array('egt',$start_time);
        }

        if($request['type'] != 1&&$request['type'] != 2&&$request['type'] != 3){

        }else{
            $where['type'] = $request['type'];
        }
        $where['status'] = array('neq',9);
        $field = 'id as pay_log_id, title, type,price, create_time';
        $order = 'create_time desc';
        $res = $this ->easyMysql('PayLog','4',$where,'',$field,$order,$request['p']);
        if(!$res){
            $res = array();
        }else{
            foreach($res as $k => $v){
                $res[$k]['create_time'] = date('m-d H:i',$v['create_time']);
            }
        }
        apiResponse('1','',$res);
    }

    /*
     * 评价列表
     * 大师token   token
     * 评价类型    type  1  商品评价  2  服务订单
     * 分页参数    p
    */
    public function commentList($request = array()){
        $master = $this ->searchMaster($request['token']);

        if($request['type'] == 1){
            $where = array('comment.status'=>array('neq',9), 'goods.master_id'=>$master['id']);
            $field = 'comment.id as comment_id, comment.evaluate_star as rank, member.nickname, member.head_pic, goods.goods_name, comment.create_time, comment.content, comment.content_pic as picture, comment.anonymous';
            $order = 'comment.create_time desc';
            $result = D('OrderComment') ->selectComment($where , $field, $order, $request['p']);
        }else{
            $where = array('comment.status'=>array('neq',9), 'comment.master_id'=>$master['id']);
            $field = 'comment.id as comment_id, comment.rank, comment.content, comment.picture, comment.create_time, member.nickname, member.head_pic, comment.anonymous';
            $order = 'comment.create_time desc';
            $result = D('Comment') ->selectComment($where , $field, $order, $request['p']);
        }

        if(!$result){
            $result = array();
        }else{
            foreach($result as $k =>$v){
                unset($head_pic);
                unset($picture);
                $head_pic = $this ->searchPhoto($v['head_pic']);
                $result[$k]['head_pic'] = $head_pic?$head_pic:C('API_URL').'/Uploads/Member/default.png';
                $result[$k]['create_time'] = date('Y-m-d',$v['create_time']);
                if(!empty($v['picture'])){
                    $picture = explode(',',$v['picture']);
                    $pic = array();
                    foreach($picture as $key =>$val){
                        $photo = $this ->searchPhoto($val);
                        $pic[$key]['pic'] = $photo?$photo:'';
                    }
                }
                $result[$k]['picture'] = $pic?$pic:array();
                if($v['anonymous'] == 1){
                    $result[$k]['head_pic'] = C('API_URL').'/Uploads/Member/default.png';
                    $result[$k]['nickname'] = '匿名用户';
                }
            }
        }

        apiResponse('1','',$result);
    }

    /**************************一道华丽的分割线*****************************/

    /**
     * 设置页
     */
    public function setupPage($request = array()){
        //查询用户标识
        $master = $this ->searchMaster($request['token']);

        $result['account']  = $master['account'].'';
//        $result['push_message'] = $member['push_message'].'';

        $where['open_type'] = 1;
        $where['user_type'] = 2;
        $where['user_id']   = $master['id'];
        $where['status']    = array('neq',9);
        $wxin = $this ->easyMysql('OpenAccount','3',$where,'','id as o_id, open_type, open_id, nickname as open_nickname');
        $where['open_type'] = 2;
        $qq   = $this ->easyMysql('OpenAccount','3',$where,'','id as o_id, open_type, open_id, nickname as open_nickname');
        $where['open_type'] = 3;
        $sina = $this ->easyMysql('OpenAccount','3',$where,'','id as o_id, open_type, open_id, nickname as open_nickname');
        if($wxin){
            $wxin['status'] = '1';
        }else{
            $wxin['status'] = '2';
        }
        if($qq){
            $qq['status'] = '1';
        }else{
            $qq['status'] = '2';
        }
        if($sina){
            $sina['status'] = '1';
        }else{
            $sina['status'] = '2';
        }
        $result['wxin'] = $wxin;
        $result['qq']   = $qq  ;
        $result['sina'] = $sina;

        apiResponse('1','',$result);
    }


    /*
    * 绑定新手机号第一步
    * 用户ID   m_id
    * 验证码   verify
    */
    public function  bindPhoneOne($request = array()){
        $master = $this ->searchMaster($request['token']);
        //验证旧手机号
        $where['id']      = $master['id'];
        $where['account'] = $request['account'];
        $where['status']  = array('neq',9);
        $result = $this ->easyMysql('Master','3',$where);
        if(!$result){
            apiResponse('0','用户信息有误');
        }
        //验证码是否正确
        $this ->_checkVerify($request['account'],$request['verify'],'bind');
        apiResponse('1','验证成功');
    }

    /*
     * 绑定新手机号第二步
     * 用户ID   m_id
     * 新手机号 account
     * 验证码   verify
    */
    public function  bindPhoneTwo($request = array()){
        $master = $this ->searchMaster($request['token']);
        //查询新手机号码是否被注册
        $where['account'] = $request['account'];
        $where['id']      = array('neq',$master['id']);
        $where['status']  = array('neq',9);
        $result = $this ->easyMysql('Master','3',$where);
        if($result){
            apiResponse('0','该手机已被绑定');
        }
        //查询验证码发送是否成功
        $this ->_checkVerify($request['account'],$request['verify'],'new_bind');
        //绑定手机
        unset($where);
        $where['id']         = $master['id'];
        $data['account']     = $request['account'];
        $data['update_time'] = time();
        $result = $this ->easyMysql('Master','2', $where, $data);
        if(!$result){
            apiResponse('0','绑定手机失败');
        }
        apiResponse('1','手机绑定成功',$request['account']);
    }

    /*
    * 修改密码
    * 用户信息 token
    * 原密码   password
    * 新密码   new_password
    * 再次输入密码  sec_password
    */
    public function  Modifypassword($request = array()){
        $master = $this ->searchMaster($request['token']);

        unset($where);
        if($master['password'] == ''){
            $where['token']    = $request['token'];
            $where['status']   = array('neq',9);
            $data['password']  = md5($request['new_password']);
            $data['update_time'] = time();
        }else{
            if(empty($request['password'])){
                apiResponse('0','原密码不能为空');
            }
            //测试原密码是否错误
            $where['token']    = $request['token'];
            $where['password'] = md5($request['password']);
            $where['status']   = array('neq',9);
            $result = M('Master') ->where($where) ->find();
            if(!$result){
                apiResponse('0','原密码输入错误');
            }
            $data['password']  = md5($request['new_password']);
            $data['update_time'] = time();
        }
        $result_data = M("Master") ->where($where) ->data($data) ->save();
        if(!$result_data){
            apiResponse('0','修改密码失败');
        }
        apiResponse('1','修改密码成功');
    }

    /**
     * 先登录再绑定三方账号
     */
    public function bindThirdAccount($request = array()){
        $master = $this ->searchMaster($request['token']);
        //验证绑定的三方账号是否存在
        $where['open_id'] = $request['open_id'];
        $where['open_type'] = $request['open_type'];
        $where['user_type'] = 2;
        $where['status']  = array('neq',9);
        $where['user_id']  = array('neq',0);
        $open_account = $this ->easyMysql('OpenAccount','3',$where);
        if(!empty($open_account)){
            apiResponse('0','该三方账号已被绑定',$open_account);
        }
        //把用户之前绑定的三方账号删除
        unset($where);
        $where['user_id'] = $master['id'];
        $where['user_type'] = 2;
        $where['open_type'] = $request['open_type'];
        $result = $this ->easyMysql('OpenAccount','3',$where);
        if($result){
            $data['status'] = 9;
            $data['update_time'] = time();
            $res = $this ->easyMysql('OpenAccount', '2', $where, $data);
        }
        //绑定新的三方账号
        unset($data);
        $data['open_type'] = $request['open_type'];
        $data['open_id']   = $request['open_id'];
        $data['user_type'] = 2;
        $data['user_id']   = $master['id'];
        $data['create_time'] = time();
        $data['nickname']  = $request['nickname'];
        $open_status = $this ->easyMysql('OpenAccount','1','',$data);
        if(!$open_status){
            apiResponse('0','绑定三方账号失败');
        }else{
            apiResponse('1','绑定三方账号成功',$request);
        }
    }

    /**
     * 进跳转页  lanesra  arsenal
     */
    public function orderType($request = array()){
        $master = $this ->searchMaster($request['token']);
        if($request['order_type'] == 1){
            $start_time = strtotime(date('Y-m-d'));
            $order['create_time'] = array('egt',$start_time);
            $service['create_time'] = array('egt',$start_time);
        }elseif($request['order_type'] == 2){
            $order['pay_status'] = 1;
            $order['order_status'] = 1;
            $service['pay_status'] = 1;
            $service['s_order_status'] = 1;
        }else{
            $order['order_status'] = array('in','1,2,3,6');
            $service['s_order_status'] = array('in','1,2,3,6');
        }
        $order['master_id'] = $master['id'];
        $order['status'] = array('lt',9);
        $service['master_id'] = $master['id'];
        $service['status'] = array('lt',9);

        $order_num = $this ->easyMysql('Order','6',$order);
        $service_num = $this ->easyMysql('ServiceOrder','6',$service);

        $result['order_num'] = $order_num?$order_num.'':'0';
        $result['service_num'] = $service_num?$service_num.'':'0';

        apiResponse('1','',$result);
    }


    /**
     * 获取支付宝账号
     */
    public function takeAlipay($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where = array('user_type'=>2, 'user_id'=>$master['id'], 'type'=>2);
        $field = 'id as withdraw_id, alipay_name, alipay_account';
        $order = 'create_time desc';
        $alipay = $this ->easyMysql('Withdraw', '3', $where, '', $field, $order);
        if(!$alipay){
            $alipay['withdraw_id'] = '';
            $alipay['alipay_name'] = '';
            $alipay['alipay_account'] = '';
        }

        apiResponse('1','',$alipay);
    }
}