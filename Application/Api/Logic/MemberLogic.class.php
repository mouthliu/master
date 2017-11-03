<?php
namespace Api\Logic;
/**
 * Class MemberLogic
 * @package Api\Logic
 * 用户模块
 */
class MemberLogic extends BaseLogic{
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
            apiResponse('error','验证码错误');
        }

        //检查验证码是否过期
        if($sms_info['expire_time']<time()){
            apiResponse('error','对不起,您的验证码已过期');
        }
    }
    /*
    * 用户首页
    * 用户ID   m_id
    */
    public function  memberCenter($request = array()){
        //查询用户标识
        $member = $this ->searchMember($request['token']);
        //查询用户的图片
        $path = M('File') ->where(array('id'=>$member['head_pic'])) ->getField('path');
        $result['nickname'] = $member['nickname'];
        $result['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Member/default.png';
        if($member['password'] != ''){
            $result['passwd'] = '1';
        }else{
            $result['passwd'] = '2';
        }
        $result['balance']  = $member['balance'].'';
        $result['integral'] = $member['integral'].'';
        $result['easemob_account']  = $member['easemob_account'].'';
        $result['easemob_password'] = $member['easemob_password'].'';
        //获取用户的优惠券
        $where['m_id']     = $member['id'];
        $where['status']   = 0;
        $where['end_time'] = array('gt',time());
        $coupon = $this ->easyMysql('MemberCoupon', '6', $where);
        $result['coupon']    = $coupon?$coupon.'':'0';
        $result['service_line'] = M('Config') ->where(array('id'=>55)) ->getField('value');

        //查询各种订单数量
        unset($where);
        $where['m_id'] = $member['id'];
        $where['status'] = array('lt',9);
        $where['order_status'] = 1;
        $order_one = $this ->easyMysql('Order','6',$where);
        $where['order_status'] = 2;
        $order_two = $this ->easyMysql('Order','6',$where);
        $where['order_status'] = 3;
        $order_three = $this ->easyMysql('Order','6',$where);
        $result['order_one'] = $order_one?$order_one.'':'0';
        $result['order_two'] = $order_two?$order_two.'':'0';
        $result['order_three'] = $order_three?$order_three.'':'0';
        unset($where['order_status']);
        $where['s_order_status'] = 1;
        $s_order_one = $this ->easyMysql('ServiceOrder','6',$where);
        $where['s_order_status'] = 2;
        $s_order_two = $this ->easyMysql('ServiceOrder','6',$where);
        $where['s_order_status'] = 4;
        $s_order_three = $this ->easyMysql('ServiceOrder','6',$where);
        $result['s_order_one'] = $s_order_one?$s_order_one.'':'0';
        $result['s_order_two'] = $s_order_two?$s_order_two.'':'0';
        $result['s_order_three'] = $s_order_three?$s_order_three.'':'0';

        apiResponse('1','',$result);
    }

    /**
     * 设置页
     */
    public function setupPage($request = array()){
        //查询用户标识
        $member = $this ->searchMember($request['token']);

        $path = $this ->easyMysql('File','5',array('id'=>$member['head_pic']),'','path');

        $result['nickname'] = $member['nickname'];
        $result['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Member/default.png';
        $result['sex']      = $member['sex'].'';
        $result['birthday'] = $member['birthday'];
        $result['account']  = $member['account'].'';
        $result['push_message'] = $member['push_message'].'';
        if($member['province'] == 0 || $member['city'] == 0 || $member['area'] == 0){
            $result['area_info'] = '暂未设置';
        }else{
            $province = $this ->easyMysql('Region','5',array('id'=>$member['province']),'','region_name');
            $city = $this ->easyMysql('Region','5',array('id'=>$member['city']),'','region_name');
            $area = $this ->easyMysql('Region','5',array('id'=>$member['area']),'','region_name');
            $result['area_info'] = $province.$city.$area.'';
        }
        $where['open_type'] = 1;
        $where['user_type'] = 1;
        $where['user_id']   = $member['id'];
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
    * 个人资料
    * 用户信息  token
    */
    public function  memberInfo($request = array()){
        $member = $this ->searchMember($request['token']);
        $result['nickname'] = $member['nickname'];
        $path = $this ->easyMysql('File','5',array('id'=>$member['head_pic']),'','path');
        $result['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Member/default.png';
        $result['sex']      = $member['sex'];
        $result['birthday'] = $member['birthday'];
        $result['account']  = $member['account'];
        if($member['province'] == 0 || $member['city'] == 0 || $member['area'] == 0){
            $result['area_info'] = '暂未设置';
        }else{
            $province = $this ->easyMysql('Region','5',array('id'=>$member['province']),'','region_name');
            $city = $this ->easyMysql('Region','5',array('id'=>$member['city']),'','region_name');
            $area = $this ->easyMysql('Region','5',array('id'=>$member['area']),'','region_name');
            $result['area_info'] = $province.$city.$area.'';
            $result['city_id'] = $member['city'];
        }

        apiResponse('1','',$result);
    }

    /*
     * 修改个人资料
     * 用户信息    token
     * 用户昵称    nickname
     * 用户头像    head_pic
     * 用户签名    sign
     * 所在省      province
     * 所在市      city
     * 所在地区    area
     * 用户性别    sex
     * 生日        birthday
     * 是否推送    push_message
    */
    public function  modifyMemberInfo($request = array()){
        $member = $this ->searchMember($request['token']);
        //用户昵称可以为空
        if(!empty($request['nickname'])){
            $data['nickname'] = $request['nickname'];
        }
        //上传图片可以为空
        if(!empty($_FILES['head_pic']['name'])){
            $res = api('UploadPic/upload', array(array('save_path' => 'Member')));
            foreach ($res as $value) {
                $head_pic = $value['id'];
                $data['head_pic'] = $head_pic;
            }
        }
        //用户所在省可以为空
        if(!empty($request['province'])){
            $data['province'] = $request['province'];
        }
        //用户所在市可以为空
        if(!empty($request['city'])){
            $data['city'] = $request['city'];
        }
        //用户地区可以为空
        if(!empty($request['area'])){
            $data['area'] = $request['area'];
        }
        //用户性别可以为空
        if(!empty($request['sex'])){
            $data['sex'] = $request['sex'];
        }
        //用户生日可以为空
        if(!empty($request['birthday'])){
            $data['birthday'] = $request['birthday'];
        }
        //是否推送可以为空
        if(!empty($request['push_message'])){
            $data['push_message'] = $request['push_message'];
        }
        //是否推送可以为空
        if(!empty($request['month'])){
            $data['month'] = $request['month'];
        }
        $data['update_time'] = time();
        $where['id'] = $member['id'];
        $result_data = $this ->easyMysql('Member','2',$where,$data);
        if(!$result_data){
            apiResponse('0','修改个人资料失败');
        }
        unset($data);
        //修改个人资料成功后返回头像
        $last_head_pic = M('Member')->where(array('token'=>$request['token']))->getField('head_pic');
        if($last_head_pic){
            $path = M('File')->where(array('id'=>$last_head_pic))->getField('path');
            $data['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Member/default.png';
        }else{
            $data['head_pic'] = C('API_URL').'/Uploads/Member/default.png';
        }
        apiResponse('1','修改个人资料成功',$data);
    }

    /*
    * 绑定新手机号第一步
    * 用户ID   m_id
    * 验证码   verify
    */
    public function  bindPhoneOne($request = array()){
        $member = $this ->searchMember($request['token']);
        //验证旧手机号
        $where['id']      = $member['id'];
        $where['account'] = $request['account'];
        $where['status']  = array('neq',9);
        $result = $this ->easyMysql('Member','3',$where);
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
        $member = $this ->searchMember($request['token']);
        //查询新手机号码是否被注册
        $where['account'] = $request['account'];
        $where['id']      = array('neq',$member['id']);
        $where['status']  = array('neq',9);
        $result = $this ->easyMysql('Member','3',$where);
        if($result){
            apiResponse('0','该手机已被绑定');
        }
        //查询验证码发送是否成功
        $this ->_checkVerify($request['account'],$request['verify'],'new_bind');
        //绑定手机
        unset($where);
        $where['id']         = $member['id'];
        $data['account']     = $request['account'];
        $data['update_time'] = time();
        $result = $this ->easyMysql('Member','2', $where, $data);
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
        $member = $this ->searchMember($request['token']);

        unset($where);
        if($member['password'] == ''){
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
            $result = M('Member') ->where($where) ->find();
            if(!$result){
                apiResponse('0','原密码输入错误');
            }
            $data['password']  = md5($request['new_password']);
            $data['update_time'] = time();
        }
        $result_data = M("Member") ->where($where) ->data($data) ->save();
        if(!$result_data){
            apiResponse('0','修改密码失败');
        }
        apiResponse('1','修改密码成功');
    }

    /*
     * 我的钱包
     * 用户信息  token
     */
    public function myWallet($request = array()){
        $member = $this ->searchMember($request['token']);
        $result['balance'] = $member?$member['balance'].'':'0.00';
        apiResponse('1','',$result);
    }

    /**
     * 充值页面
     */
    public function rechargePage($request = array()){
        $member = $this ->searchMember($request['token']);
        $recharge = $this ->easyMysql('Recharge', '4', '', '', 'id as re_id, ch_price', 'ch_price asc','','6');
        if(!$recharge){
            $recharge = array();
        }
        apiResponse('1','',$recharge);
    }

    /*
     * 用户充值
     * 充值方式       type   1  支付宝  2  微信  3  银行卡
     * 充值金额       price
     */
    public function recharge($request = array()){
        $member = $this ->searchMember($request['token']);
        if(!empty($request['ch_id'])){
            $recharge = $this ->easyMysql('Recharge','3',array('id'=>$request['ch_id']));
            if(!$recharge){
                apiResponse('0','充值金额有误');
            }
            $price = $recharge['ch_price'];
        }else{
            if($request['price'] < 0.01){
                apiResponse('0','充值金额有误');
            }
            $price = $request['price'];
        }
        //充值现金
        $order_sn = date('Ymd').rand(1000000,9999999);
        $data['m_id']        = $member['id'];
        $data['ch_id']       = $request['ch_id']?$request['ch_id']:'0';
        $data['price']       = $price;
        $data['pay_type']    = $request['type'];
        $data['order_sn']    = $order_sn;
        $data['create_time'] = time();
        $rechange = M('MemberRecharge') ->add($data);
        if(!$rechange){
            apiResponse('0','充值失败');
        }
        $result_data['recharge_id'] = $rechange;
        $result_data['order_sn'] = ''.$order_sn;
        $result_data['price']    = ''.$price;
        apiResponse('1','充值成功',$result_data);
    }

    /*
    * 用户提现页面
    * 用户标识      token
    */
    public function withdrawPage($request = array()){
        $member = $this ->searchMember($request['token']);

        $where['bank.user_type'] = 1;
        $where['bank.user_id']   = $member['id'];
        $where['bank.status']    = array('neq',9);
        $field = 'bank.id as bank_id, bank.bank_number, support_bank.bank_name';
        $bank = D('Member') ->selectBank($where, $field);

        if(!$bank){
            $bank['bank_id'] = '';
            $bank['bank_number'] = '';
            $bank['bank_name'] = '';
        }else{
            $bank_number = substr($bank['bank_number'],-4,4);
            $bank['bank_number'] = '**** **** **** '.$bank_number;
        }

        $result['bank'] = $bank;
        $result['balance'] = $member['balance'];

        apiResponse('1','',$result);
    }

    /*
    * 用户提现
     * 银行卡ID    bank_id
     * 取款金额    price
     * 支付宝账号  alipay_account
     * 支付宝姓名  alipay_name
     * 微信三方账号    wxin_account
     * 提现类型    type  1  支付宝  2  微信  3  支付宝
    */
    public function withdraw($request = array()){
        $member = $this ->searchMember($request['token']);

        if($member['balance'] - $request['price'] < 0){
            apiResponse('0','账号余额不足');
        }

        if($request['type'] == 1){
            $bank = M('Bank') ->where(array('id'=>$request['bank_id'],'user_type'=>1,'user_id'=>$member['id'],'status'=>array('neq',9))) ->find();
            if(!$bank){
                apiResponse('0','银行卡状态有误');
            }
        }

        $data['user_id']     = $member['id'];
        $data['user_type']   = 1;
        $data['bank_id']     = $request['bank_id']?$request['bank_id']:'';
        $data['price']       = $request['price'];
        $data['create_time'] = time();
        $data['type']        = $request['type'];
        $data['alipay_account'] = $request['alipay_account']?$request['alipay_account']:'';
        $data['alipay_name'] = $request['alipay_name']?$request['alipay_name']:'';
        $withdraw = M('Withdraw') ->add($data);
        if(!$withdraw){
            apiResponse('0','提现失败');
        }
        $member_data = M('Member') ->where(array('token'=>$request['token'],'status'=>array('neq',9))) ->setDec('balance',$request['price']);
        if(!$member_data){
            apiResponse('0','用户减款失败');
        }

        //添加账单明细
        unset($data);
        $data['user_type']   = 1;
        $data['user_id']     = $member['id'];
        $data['type']        = 2;
        $data['title']       = '提现';
        $data['price']       = $request['price'];
        $data['symbol']      = 2;
        $data['create_time'] = time();
        $detail = $this ->easyMysql('Detail','1','',$data);

        apiResponse('1','提现成功');
    }

    /**
     * @param array $request
     * 账单明细
     */
    public function detail($request = array()){
        $member = searchMember($request['token']);
        $where['user_type'] = 1;
        $where['user_id'] = $member['id'];
        $detail = $this ->easyMysql('Detail','4',$where,'','*','create_time desc',$request['p'],'');
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
    * 红包管理
     * 红包类型  1未使用  2已使用  3已失效
    */
    public function couponList($request = array()){
        $token = $_SERVER['HTTP_TOKEN'];
        $member = $this ->searchMember($token);
        if($request['type'] != 1&&$request['type'] != 2&&$request['type'] != 3){
            apiResponse('300','请选择红包类型');
        }
        if($request['type'] == 1){
            $where['m_id'] = $member['id'];
            $where['start_time'] = array('elt',time());
            $where['end_time'] = array('egt',time());
            $where['status'] = 0;
        }elseif($request['type'] == 2){
            $where['m_id'] = $member['id'];
            $where['start_time'] =  array('elt',time());
            $where['end_time'] = array('egt',time());
            $where['status'] = 1;
        }else{
            $where['m_id'] = $member['id'];
            $where['end_time'] = array('elt',time());
            $where['status'] = 0;
        }
        $coupon = M('MemberCoupon') ->where($where) ->field('id as coupon_id, title, price, end_time') ->select();
        if(!$coupon){
            apiResponse('1','您还没有红包呐',array());
        }
        foreach($coupon as $k => $v){
            $coupon[$k]['end_time'] = date('Y-m-d',$v['end_time'] - 1);
        }

        apiResponse('1','',$coupon);
    }

    /**
     * 先登录再绑定三方账号
     */
    public function bindThirdAccount($request = array()){
        $member = $this ->searchMember($request['token']);
        //验证绑定的三方账号是否存在
        $where['open_id'] = $request['open_id'];
        $where['open_type'] = $request['open_type'];
        $where['user_type'] = 1;
        $where['status']  = array('neq',9);
        $where['user_id']  = array('neq',0);
        $open_account = $this ->easyMysql('OpenAccount','3',$where);
        if(!empty($open_account)){
            apiResponse('0','该三方账号已被绑定');
        }
        //把用户之前绑定的三方账号删除
        unset($where);
        $where['user_id'] = $member['id'];
        $where['user_type'] = 1;
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
        $data['user_type'] = 1;
        $data['user_id']   = $member['id'];
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
     * 用户积分列表
     * 用户token    token
     * 分页参数     p
     */
    public function integralList($request = array()){
        $member = $this ->searchMember($request['token']);
        $where  = array('m_id'=>$member['id'],'status'=>array('neq',9));
        $field  = 'id as integral_id, type, title, integral, symbol, create_time';
        $order  ='create_time desc';
        $integral = $this ->easyMysql('Integral',4,$where,'',$field,$order,$request['p']);

        if(!$integral){
            $integral = array();
        }

        foreach($integral as $k => $v){
            $integral[$k]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
        }

        apiResponse('1','',$integral);
    }
}