<?php
namespace Api\Logic;
/**
 * Class RegisterLogLogic
 * @package Api\Logic
 * 登录注册模块
 */
class RegisterLogLogic extends BaseLogic{
    /*
     * 发送验证码
     * 手机账号    account
     * 发送类型    type    retrieve
     * 用户类型    user_type  1  用户  2  大师
     */
    public function sendVerify($request = array()){
        $where['account'] = $request['account'];
        $where['status']  = array('neq',9);
        if($request['user_type'] == 1){
            $result = $this ->easyMysql('Member','3', $where);
        }else{
            $result = $this ->easyMysql('Master','3', $where);
        }

        if($request['type'] == 'new_bind'){
            //type 为重新绑定状态下 用户账号不能存在
            if($result){
                apiResponse('0','该账号已经绑定');
            }
        }elseif($request['type'] == 'activate'){
            //type 为注册状态下 用户账号不能存在
            if($result){
                apiResponse('0','该账号已被注册');
            }
        }elseif($request['type'] == 'retrieve'){
            //type 为找回状态下 用户账号必须存在
            if(empty($result)){
                apiResponse('0','该账号未被注册');
            }
        }
        $result = D('Sms')->sendVerify($request['account'],$request['type']);
        if($result['success']){
            apiResponse('1',$result['success']);
        }
        apiResponse('0',$result['error']);
    }

    /*
     * 用户注册
     * 手机账号    account
     * 验证码      verify
     * 登录密码    password
     */
    public function  register($request = array()){
        $where['account'] = $request['account'];
        $where['status']  = array('neq',9);
        if($request['user_type'] == 1){
            $result = $this ->easyMysql('Member','3', $where);
        }else{
            $result = $this ->easyMysql('Master','3', $where);
        }

        if($result){
            apiResponse('0','该账号已被注册');
        }
        $this ->_checkVerify($request['account'],$request['verify'],'activate');
        //注册环信
        $easemob_reg_info = $this->easemobRegister();
        if($easemob_reg_info['flag'] == 'error'){
            apiResponse('0','用户注册失败');
        }
        $data['account']          = $request['account'];
        $data['password']         = md5($request['password']);
        $data['easemob_account']  = $easemob_reg_info['easemob_account'];
        $data['easemob_password'] = $easemob_reg_info['easemob_password'];
        $data['lat']              = $request['lat']?$request['lat']:'0.00';
        $data['lng']              = $request['lng']?$request['lng']:'0.00';
        $data['nickname']         = randomKey(5,8);
        $data['sex']              = 3;
        $data['push_message']     = 2;
        $data['create_time']      = time();
        $data['status']           = 1;
        $data['token']            = md5(time().rand(10000,99999));
        $data['expire_time']      = strtotime(date('Y-m-d',time())) + 15*86400;
        if($request['user_type'] == 1){
            //新增数据
            $result = $this ->easyMysql('Member','1', '', $data);
            if(empty($result)){
                apiResponse('0','注册失败');
            }
            $result_data['account']  = $data['account'];
            $result_data['token']    = $data['token'];
            $result_data['head_pic'] = C('API_URL').'/Uploads/Member/default.png';
            $result_data['nickname'] = ''.$data['nickname'];
            $result_data['passwd']   = '1';
            $result_data['coupon']   = '0';
            $result_data['easemob_account'] = $data['easemob_account'].'';
            $result_data['easemob_password'] = $data['easemob_password'].'';
            $result_data['balance']  = '0.00';
            $result_data['integral'] = '0';
        }else{
            $data['score'] = 5;
            $result = $this ->easyMysql('Master','1','',$data);
            if(empty($result)){
                apiResponse('0','注册失败');
            }
            $result_data['account']  = $data['account'];
            $result_data['token']    = $data['token'];
            $result_data['head_pic'] = C('API_URL').'/Uploads/Master/default.png';
            $result_data['nickname'] = $data['nickname'];
            $result_data['passwd']   = '1';
            $result_data['easemob_account'] = $data['easemob_account'].'';
            $result_data['easemob_password'] = $data['easemob_password'].'';
            $result_data['balance']  = '0.00';

            $result_data['auth_status'] = '0';
            $result_data['social_name'] = '';
            $result_data['field']       = '';
            $result_data['score']       = '5';
            $result_data['total_order'] = '0';
            $result_data['today_balance'] = '0.00';
            $result_data['today_order'] = '0';
            $result_data['new_order']   = '0';
            $result_data['service_order']   = '0';
            $result_data['order']   = '0';
            $result_data['unfinish_order'] = '0';
            $result_data['reward_order'] = '0';
            $result_data['sex']       = '3';
            $result_data['introduction'] = '';
            $result_data['city_name'] = '';
            $result_data['social_status'] = '2';
        }

        $result_data['service_line'] = M('Config') ->where(array('id'=>55)) ->getField('value');
        apiResponse('1','注册成功',$result_data);
    }

    /*
     * 用户登录
     * 用户账号    account
     * 用户密码    password
     */
    public function Login($request = array()){
        $where['account']  = $request['account'];
//        $where['password'] = md5($request['password']);
        //对该账号进行查询
        if($request['user_type'] == 1){
            $result = $this ->easyMysql('Member','3', $where);
        }else{
            $result = $this ->easyMysql('Master','3', $where);
        }

        if(empty($result)){
            apiResponse('0','该账号不存在');
        }elseif($result['status'] == '9'){
            apiResponse('0','该账号信息已被删除');
        }elseif($result['status'] != '1'){
            apiResponse('0','该账号已被禁用');
        }elseif($result['password'] != md5($request['password'])){
            apiResponse('0','该账号密码错误');
        }

        //每次登陆的时候   过期时间永远更新
        $data['lat']         = $request['lat']?$request['lat']:'0.00';
        $data['lng']         = $request['lng']?$request['lng']:'0.00';
        $data['expire_time'] = strtotime(date('Y-m-d',time())) + 15*86400;
        $data['update_time'] = time();
        if($request['user_type'] == 1){
            $res = $this ->easyMysql( 'Member', '2', $where, $data);

            //返回用户ID  用户账号  用户头像  用户昵称
            $result_data['account']  = $result['account'];
            $result_info['token']  = ''.$result['token'];
            $result_info['nickname'] = ''.$result['nickname'];
            $result_info['passwd']   = '1';
            $result_info['easemob_account'] = ''.$result['easemob_account'];
            $result_info['easemob_password'] = ''.$result['easemob_password'];
            $path = M('File')->where(array('id'=>$result['head_pic']))->getField('path');
            $result_info['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Member/default.png';
            $result_info['balance']  = $result['balance']?''.$result['balance']:'0.00';
            $result_info['integral']  = $result['integral']?''.$result['integral']:'0';

            //获取用户优惠券的数量
            unset($where);
            $where['m_id']   = $result['id'];
            $where['status'] = array('eq',0);
            $where['end_time'] = array('gt',time());
            $coupon_num = M('MemberCoupon')->where($where)->count();
            $result_info['coupon'] = $coupon_num?''.$coupon_num:'0';
        }else{
            $res = $this ->easyMysql( 'Master', '2', $where, $data);

            //返回大师token  大师昵称  大师是否有密码  大师环信  环信密码  大师头像  大师余额
            $result_data['account']  = $result['account'];
            $result_info['token']    = ''.$result['token'];
            $result_info['nickname'] = ''.$result['nickname'];
            $result_info['passwd']   = '1';
            $result_info['easemob_account']  = ''.$result['easemob_account'];
            $result_info['easemob_password'] = ''.$result['easemob_password'];
            $path = M('File')->where(array('id'=>$result['head_pic']))->getField('path');
            $result_info['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Master/default.png';
            $result_info['balance']  = $result['balance']?''.$result['balance']:'0.00';
            $result_info['sex']  = $result['sex']?''.$result['sex']:'3';
            $result_info['introduct']  = $result['introduct']?$result['introduct']:'';

            if($result['city'] != '0'){
                $result_info['city_name'] = $this ->easyMysql('Region','5',array('id'=>$result['city']),'','region_name');
            }else{
                $result_info['city_name'] = '';
            }
            //大师类别  是否认证  协会名称  评分
            $result_info['auth_status'] = $result['auth_status'];

            if($result['social_id'] != 0){
                $result_info['social_status'] = '1';
                $result_info['social_name'] = $this ->getFieldSth('Social',array('id'=>$result['social_id']),'social_name');
            }else{
                $result_info['social_status'] = '2';
                $result_info['social_name'] = '';
            }
            if($result['field_id'] != ''){
                $result_info['field'] = $this ->findFields($result['field_id']);
            }else{
                $result_info['field'] = array();
            }

            $result_info['score']       = $result['score'];
            //总订单数  今日收益  今日订单  新订单  未完成订单  悬赏订单
            //获取总订单数
            unset($where);
            $where['master_id'] = $result['id'];
            $where['status']    = array('neq',10);
            $service_order = $this ->easyMysql('ServiceOrder',6,$where);
            $order = $this ->easyMysql('Order',6,$where);
            $result_info['total_order'] = ($service_order + $order).'';

            //获取今日收益
            unset($where);
            $start_time = strtotime(date('Y-m-d',time()));
            $where['master_id'] = $result['id'];
            $where['create_time'] = array('egt',$start_time);
            $today_balance = M('PayLog') ->where($where) ->field('COUNT(price) as total_price') ->find();
            $result_info['today_balance'] = $today_balance?$today_balance['total_price'].'':'0.00';

            //获取今日订单
            unset($where);
            $where['master_id'] = $result['id'];
            $where['status']    = array('neq',10);
            $where['create_time'] = array('egt',$start_time);
            $service_order = $this ->easyMysql('ServiceOrder',6,$where);
            $order = $this ->easyMysql('Order',6,$where);
            $result_info['today_order'] = ($service_order + $order).'';

            //获取新订单
            unset($where);
            $where['master_id'] = $result['id'];
            $where['status']    = array('lt',9);
            $where['s_order_status'] = 1;
            $where['pay_status'] = 1;
            $service_order = $this ->easyMysql('ServiceOrder',6,$where);
            unset($where);
            $where['master_id'] = $result['id'];
            $where['status']    = array('lt',9);
            $where['order_status'] = 1;
            $where['pay_status'] = 1;
            $order = $this ->easyMysql('Order',6,$where);
            $result_info['new_order'] = ($service_order + $order).'';
            $result_info['service_order'] = $service_order?$service_order.'':'0';
            $result_info['order'] = $order?$order.'':'0';
            //获取未完成订单
            unset($where);
            $where['master_id'] = $result['id'];
            $where['s_order_status'] = array('not in',array(4,5,7,8));
            $where['status']    = array('neq',10);
            $service_order = $this ->easyMysql('ServiceOrder',6,$where);
            unset($where);
            $where['master_id'] = $result['id'];
            $where['status']    = array('neq',10);
            $where['order_status'] = array('not in',array(4,5,7,8));
            $order = $this ->easyMysql('Order',6,$where);
            $result_info['unfinish_order'] = ($service_order + $order).'';

            //获取该大师的所有悬赏订单
            unset($where);
//            $map['master_id'] = $result['id'];
//            $map['master_id'] = 0;
//            $map['_logic'] = 'OR';
            $where['master_id'] = $result['id'];
            $where['reward_time'] = array('lt',time());
            $reward_order = $this ->easyMysql('RewardOrder',6,$where);
            $result_info['reward_order'] = $reward_order?$reward_order.'':'0';

        }
        $result_info['service_line'] = M('Config') ->where(array('id'=>55)) ->getField('value');
        apiResponse('1','登录成功',$result_info);
    }

    /*
     * 三方登录
     * 三方账号  open_id
     * 三方类型  open_type 1  用户  2  大师
     * 三方昵称  nickname
     * 三方头像  head_pic
     */
    public function  thirdLogin($request = array()){
        $where['open_id']   = $request['open_id'];
        $where['open_type'] = $request['open_type'];
        $where['user_type'] = $request['user_type'];
        $where['user_id']   = array('neq','0');
        $where['status'] = array('neq',9);
        $open_account = $this ->easyMysql('OpenAccount','3',$where);
        if(!$open_account){
            $data['open_id']   = $request['open_id'];
            $data['open_type'] = $request['open_type'];
            $data['user_type'] = $request['user_type'];
            $data['nickname']  = $request['nickname'];
            if($_FILES['head_pic']['name']){
                $res= api('UploadPic/upload',array(array('save_path'=>'Member')));
                $head_pic = '';
                foreach($res as $value){
                    $head_pic = $value['id'];
                    $path = $value['path'];
                }
            }
            $data['head_pic'] = $head_pic?$head_pic:'0';
            $data['create_time'] = time();
            $res = $this ->easyMysql('OpenAccount','1','',$data);
            if(!$res){
                apiResponse('0','三方登录失败');
            }
            $result_data['o_id'] = $res;
            $result_data['user_type'] = $request['user_type'];
        }else{
            unset($where);
            $where['id'] = $open_account['user_id'];
            if($request['user_type'] == 1){
                $res = $this ->easyMysql('Member', '3', $where);
            }else{
                $res = $this ->easyMysql('Master', '3', $where);
            }

            //把过期时间保存起来
            $data['expire_time'] = strtotime(date('Y-m-d',time())) + 15*86400;
            $data['update_time'] = time();
            if($request['user_type'] == 1){
                $member_data = $this ->easyMysql('Member', '2', $where, $data);
            }else{
                $member_data = $this ->easyMysql('Master', '2', $where, $data);
            }
            $path = $this ->easyMysql('File','5',array('id'=>$res['head_pic']),'','path');
            $result_data['token']            = $res['token'];
            $result_data['nickname']         = $res['nickname'];
            $result_data['easemob_account']  = $res['easemob_account'];
            $result_data['easemob_password'] = $res['easemob_password'];

            $result_data['balance']          = $res['balance'].'';
            if($res['password'] != ''){
                $result_data['passwd'] = '1';
            }else{
                $result_data['passwd'] = '2';
            }

            if($request['user_type'] == 1){
                $result_data['coupon']           = '0';
                $result_data['integral']         = $res['integral'].'';
                $result_data['head_pic']         = $path?C('API_URL').$path:C('API_URL').'/Uploads/Member/default.png';
            }else{
                $result_data['head_pic']         = $path?C('API_URL').$path:C('API_URL').'/Uploads/Master/default.png';
                $result_data['sex']         = $res['sex']?$res['sex']:'3';
                $result_data['introduct']   = $res['introduct']?$res['introduct']:'';
                if($res['city'] != 0){
                    $result_data['city_name'] = $this ->easyMysql('Region','5',array('id'=>$res['city']),'','region_name');
                }else{
                    $result_data['city_name'] = '';
                }

                //大师类别  是否认证  协会名称  评分
                $result_data['auth_status'] = $res['auth_status'];
                if($result_data['social_id'] != 0){
                    $result_data['social_status'] = '1';
                    $result_data['social_name'] = $this ->getFieldSth('Social',array('id'=>$res['social']),'social_name');
                }else{
                    $result_data['social_status'] = '2';
                    $result_data['social_name'] = '';
                }
                if($res['field'] != ''){
                    $result_data['field'] = $this ->findFields($res['field']);
                }else{
                    $result_data['field'] = '';
                }
                $result_data['score']       = $res['score'];
                //总订单数  今日收益  今日订单  新订单  未完成订单  悬赏订单

                //获取总订单数
                unset($where);
                $where['master_id'] = $res['id'];
                $where['status']    = array('neq',10);
                $service_order = $this ->easyMysql('ServiceOrder',6,$where);
                $order = $this ->easyMysql('Order',6,$where);
                $result_data['total_order'] = ($service_order + $order).'';
                //获取今日收益
                unset($where);
                $start_time = strtotime(date('Y-m-d',time()));
                $where['master_id'] = $res['id'];
                $where['create_time'] = array('egt',$start_time);
                $today_balance = M('PayLog') ->where($where) ->field('COUNT(price) as total_price') ->find();
                $result_data['today_balance'] = $today_balance?$today_balance['total_price'].'':'0.00';

                //获取今日订单
                unset($where);
                $where['master_id'] = $res['id'];
                $where['status']    = array('neq',10);
                $where['create_time'] = array('egt',$start_time);
                $service_order = $this ->easyMysql('ServiceOrder',6,$where);
                $order = $this ->easyMysql('Order',6,$where);
                $result_data['today_order'] = ($service_order + $order).'';


                //获取新订单
                unset($where);
                $where['master_id'] = $res['id'];
                $where['status']    = array('lt',9);
                $where['s_order_status'] = 1;
                $where['pay_status'] = 1;
                $service_order = $this ->easyMysql('ServiceOrder',6,$where);
                unset($where);
                $where['master_id'] = $res['id'];
                $where['status']    = array('lt',9);
                $where['order_status'] = 1;
                $where['pay_status'] = 1;
                $order = $this ->easyMysql('Order',6,$where);
                $result_date['new_order']   = ($service_order + $order).'';
                $result_date['service_order'] = $service_order?$service_order.'':'0';
                $result_date['order'] = $order?$order.'':'0';

                //获取未完成订单
                unset($where);
                $where['master_id'] = $res['id'];
                $where['s_order_status'] = array('not in',array(4,5,7,8));
                $where['status']    = array('neq',10);
                $service_order = $this ->easyMysql('ServiceOrder',6,$where);
                unset($where);
                $where['master_id'] = $res['id'];
                $where['status']    = array('neq',10);
                $where['order_status'] = array('not in',array(4,5,7,8));
                $order = $this ->easyMysql('Order',6,$where);
                $result_data['unfinish_order'] = ($service_order + $order).'';

                //获取该大师的所有悬赏订单
                unset($where);
//                $map['master_id'] = $res['id'];
//                $map['master_id'] = 0;
//                $map['_logic'] = 'OR';
                $where['master_id'] = $res['id'];
                $where['reward_time'] = array('lt',time());
                $reward_order = $this ->easyMysql('RewardOrder',6,$where);
                $result_data['reward_order'] = $reward_order?$reward_order.'':'0';
            }
        }

        $result_data['service_line'] = M('Config') ->where(array('id'=>55)) ->getField('value');
        apiResponse('1','登录成功',$result_data);
    }

    /*
     * 忘记密码
     * 邮箱账号  account
     * 验证码    verify
     */
    public function  forgetPassword($request = array()){
        $this->_checkVerify($request['account'],$request['verify'],'retrieve');
        $where['account'] = $request['account'];
        $where['status']  = array('neq',9);
        if($request['user_type'] == 1){
            $res = $this ->easyMysql('Member','3', $where);
        }else{
            $res = $this ->easyMysql('Master','3', $where);
        }
        if(!$res){
            apiResponse('0','用户信息有误');
        }
        $data['password'] = md5($request['password']);
        $data['update_time'] = time();
        if($request['user_type'] == 1){
            $result = $this ->easyMysql('Member','2',$where,$data);
        }else{
            $result = $this ->easyMysql('Master','2',$where,$data);
        }
        if(!$result){
            apiResponse('0','修改密码失败');
        }
        apiResponse('1','修改密码成功');
    }

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

    /**
     * 三方登录绑定账号
     */
    public function thirdBindAccount($request = array()){
        $this->_checkVerify($request['account'],$request['verify'],'bind');
        $where['id']        = $request['o_id'];
        $where['user_type'] = $request['user_type'];
        $where['status']    = array('neq',9);
        $open_account = $this ->easyMysql('OpenAccount','3',$where);

        //注册环信
        $easemob_reg_info = $this->easemobRegister();
        if($easemob_reg_info['flag'] == 'error'){
            apiResponse('0','环信注册失败');
        }
        if($request['user_type'] == 1){
            $res = $this ->easyMysql('Member','3',array('account'=>$request['account'],'status'=>array('neq',9)));
        }else{
            $res = $this ->easyMysql('Master','3',array('account'=>$request['account'],'status'=>array('neq',9)));
        }

        if(!$res){
            //新增数据
            $data['account']          = $request['account'];
            $data['easemob_account']  = $easemob_reg_info['easemob_account'];
            $data['easemob_password'] = $easemob_reg_info['easemob_password'];
            $data['nickname']         = $open_account['nickname'];
            $data['head_pic']         = $open_account['head_pic'];
            $data['lat']              = $request['lat']?$request['lat']:'0.00';
            $data['lng']              = $request['lng']?$request['lng']:'0.00';
            $data['sex']              = 3;
            $data['push_message']     = 2;
            $data['create_time']      = time();
            $data['status']           = 1;
            $data['token']            = md5(time().rand(10000,99999));
            $data['expire_time']      = strtotime(date('Y-m-d',time())) + 15*86400;
            if($request['user_type'] == 1){
                $result = $this ->easyMysql('Member','1', '', $data);
            }else{
                $data['score'] = 5;
                $result = $this ->easyMysql('Master','1', '', $data);
            }
            if(empty($result)){
                apiResponse('0','注册失败');
            }
            $path = $this ->easyMysql('File', '5', array('id'=>$open_account['head_pic']),'','path');
            $dat['user_id'] = $result;
            $dat['user_type'] = $request['user_type'];
            $dat['update_time'] = time();
            $passwd = '2';
        }else{
            $path = $this ->easyMysql('File', '5', array('id'=>$res['head_pic']),'','path');
            $dat['user_id'] = $res['id'];
            $dat['user_type'] = $request['user_type'];
            $dat['update_time'] = time();
            if($res['password'] != ''){
                $passwd = '1';
            }else{
                $passwd = '2';
            }
        }
        $res_open = $this ->easyMysql('OpenAccount','2', $where, $dat);
        if(!$res_open){
            apiResponse('0','三方登录失败');
        }

        $result_data['token']    = $res['token']?$res['token']:$data['token'];
        $result_data['nickname'] = $res['nickname']?$res['nickname'].'':''.$data['nickname'];
        $result_data['passwd']   = $passwd;
        $result_data['easemob_account']  = $res['easemob_account'] ?$res['easemob_account'].'' :$data['easemob_account'].'' ;
        $result_data['easemob_password'] = $res['easemob_password']?$res['easemob_password'].'':$data['easemob_password'].'';
        $result_data['balance']  = $res['balance']?$res['balance'].'':'0.00';
        if($request['user_type'] == 1){
            $result_data['coupon']   = '0';
            $result_data['head_pic'] = $path ?C('API_URL').$path :C('API_URL').'/Uploads/Member/default.png';
            $result_data['integral'] = $res['integral']?$res['integral'].'':'0';
        }else{
            $result_data['head_pic'] = $path ?C('API_URL').$path :C('API_URL').'/Uploads/Master/default.png';
            $result_data['sex']      = $res['sex']?$res['sex']:'3';
            $result_data['introduct'] = $res['introduct']?$res['introduct']:'';
            if($res['city'] == 0 || empty($res)){
                $result_data['city_name'] = '';
            }else{
                $result_data['city_name'] = $this ->easyMysql('Region','5',array('id'=>$res['city']),'','region_name');
            }
            //大师类别  是否认证  协会名称  评分
            $result_data['auth_status'] = $res['auth_status']?$res['auth_status']:'0';
            $result_data['social_name'] = '';
            if($result_data['social_name'] != ''){
                $result_data['social_status'] = '1';
            }else{
                $result_data['social_status'] = '2';
            }
            $result_data['field']       = '';
            $result_data['score']       = $res['score']?$res['score']:'5';
            //总订单数  今日收益  今日订单  新订单  未完成订单  悬赏订单
            $result_data['total_order'] = '0';
            $result_data['today_balance'] = '0.00';
            $result_data['today_order'] = '0';
            $result_data['new_order']   = '0';
            $result_data['order']   = '0';
            $result_data['service_order']   = '0';
            $result_data['unfinish_order'] = '0';
            $result_data['reward_order'] = '0';
        }

        $result_data['service_line'] = M('Config') ->where(array('id'=>55)) ->getField('value');
        apiResponse('1','三方登录成功',$result_data);
    }

    /**
     * 测试短信
     */
    public function testVerify(){
        /**
         * demo 测试类
         */
        $url 		= "http://www.api.zthysms.com/sendSms.do";//提交地址
        $username 	= 'dashihy';//用户名
        $password 	= 'Dashi88';//原密码
        $data = array(
            'content' 	=> '【大师】123456',//短信内容
            'mobile' 	=> '18722240456',//手机号码
            'xh'		=> ''//小号
        );
        $return = $this->sendSMS('POST','',$url, $username, $password, $data);//GET or POST
        apiResponse('error','',$return);
    }

    /**
     * @param $type|提交类型 POST/GET
     * @param $isTranscoding|是否需要转 $isTranscoding 是否需要转utf-8 默认 false
     * @return mixed
     */
    public function sendSMS($type, $isTranscoding = false, $url, $username, $password, $data) {
        $mobile = $data['mobile'];
        $data['username'] = $username;
        $data['tkey'] 	  = date('YmdHis');
        $data['password'] = md5(md5($password).$data['tkey']);
        $data['mobile']   = $mobile;
        $data['content']  = $isTranscoding === true ? mb_convert_encoding($data['content'], "UTF-8") : $data['content'];
        $data['xh']       = '';
        return  $type == "POST" ? $this->httpPost($url, $data) : $this->httpGet($url, $data);
    }

    public function httpGet($url, $data) {
        $url = $url . '?' . http_build_query($data);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        $res = curl_exec($curl);
        if (curl_errno($curl)) {
            echo 'Error GET '.curl_error($curl);
        }
        curl_close($curl);
        return $res;
    }

    public function httpPost($url, $data){ // 模拟提交数据函数

        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_POST, true); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS,  http_build_query($data)); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, false); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // 获取的信息以文件流的形式返回
        $result = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Error POST'.curl_error($curl);
        }
        curl_close($curl); // 关键CURL会话
        return $result; // 返回数据
    }

    /**
     * 大师或者用户设置密码
     */
    public function setPassword($request = array()){
        if($request['user_type'] == 1){
            $result = $this ->searchMember($request['token']);
        }else{
            $result = $this ->searchMaster($request['token']);
        }
        $data['password'] = md5($request['password']);
        $data['update_time'] = time();
        if($request['user_type'] == 1){
            $res = $this ->easyMysql('Member',2,array('id'=>$result['id']),$data);
        }else{
            $res = $this ->easyMysql('Master',2,array('id'=>$result['id']),$data);
        }
        if(!$res){
            apiResponse('0','设置密码失败');
        }

        apiResponse('1','设置成功');
    }
}