<?php
namespace Common\Api;
require_once './ThinkPHP/Library/Vendor/BasalticSms/nusoap.php';

/**
 * Class SmsApi
 * @package Common\Api
 * 消息发送接口
 */
class SmsApi {

    /*
     * 助通短信Api
     */
    public  static function sendSms($receiver,$content){

        //读取站点配置  先读取缓存
//        $config = S('Config_Cache');
//        if(!$config){
//            $config = D('Config')->parseList();
//            S('Config_Cache',$config);
//        }
//        //添加配置到 C函数
//        C($config);

        $config = D('Config')->parseList();
        S('Config_Cache',$config);

        $data['username'] = $config['SMS']['ACCOUNT'];
        $data['tkey']     = date('YmdHis');
        $passwd = $config['SMS']['PASSWORD'];
        $data['password'] = md5(md5($passwd).$data['tkey']);
        $data['mobile']	  = $receiver;	//号码
        $data['content']  = '【大师】'.$content;		//内容
        $data['content']  = iconv("UTF-8", "UTF-8", $data['content']);
        $data['xh']       = '';
        $url = 'http://www.api.zthysms.com/sendSms.do';
        $sms_content = httpPost($url, $data);
        $sms_response   = explode(",",$sms_content);  //处理返回信息
        if($sms_response[0] != 1) {
            return array('error' => '发送失败');
        } else {
            return true;
        }
    }

    /**
     * 发送短信
     * 创蓝短信
     * @param string $mobile 		手机号码
     * @param string $msg 			短信内容
     * @param string $needstatus 	是否需要状态报告
     */
//    public function sendSms( $receiver,$content1) {
//        //读取站点配置  先读取缓存
//        $config = D('Config')->parseList();
//        $account =$config['SMS']['ACCOUNT'];		//用户账号
//        $password = $config['SMS']['PASSWORD'];		//密码
//        $mobile	 = $receiver;	//号码
//        $content = $content1;		//内容
//
//        //创蓝接口参数
//        $postArr = array (
//            'un' => self::API_ACCOUNT,
//            'pw' => self::API_PASSWORD,
//            'msg' => $content,
//            'phone' => $mobile,
//            'rd' => '1'
//        );
//
//        $result = curlPost( self::API_SEND_URL , $postArr);
//        $res = explode(',',$result);
//        if($res[1] == 0){
//            return true;
//        }else{
//            return false;
//        }
//    }
}