<?php
/**
 * API返回信息格式函数
 * @param string $flag
 * @param string $message
 * @param string $data
 */
function apiResponse($flag = 'error', $message = '',$data = array()){
    $result = array('flag'=>$flag,'message'=>$message,'data'=>$data);
    print json_encode($result,JSON_UNESCAPED_UNICODE);exit;
}

/**
 * API返回信息格式函数
 * @param string $flag
 * @param string $message
 * @param string $data
 */
function apiResponse2($flag = 'error', $message = '',$data = array(),$nums){
    header('Access-Control-Allow-Origin: *');
    header('Content-Type:application/Json; charset=utf-8');
    $result = array('flag'=>$flag,'message'=>$message,'data'=>$data,'nums'=>$nums);
    die(json_encode($result,JSON_UNESCAPED_UNICODE));
//    print json_encode($result);exit;
}

/**
 * @param $lat1
 * @param $lng1
 * @param $lat2
 * @param $lng2
 * @param $precision
 * @return float
 * 根据经纬度计算距离
 */
function getDistance($lat1, $lng1, $lat2, $lng2,$precision) {
    $earthRadius = 6367000; //approximate radius of earth in meters

    /*
    Convert these degrees to radians
    to work with the formula
    */

    $lat1 = ($lat1 * pi() ) / 180;
    $lng1 = ($lng1 * pi() ) / 180;

    $lat2 = ($lat2 * pi() ) / 180;
    $lng2 = ($lng2 * pi() ) / 180;

    $calcLongitude = $lng2 - $lng1;
    $calcLatitude = $lat2 - $lat1;
    $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
    $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
    $calculatedDistance = $earthRadius * $stepTwo;

    return round($calculatedDistance/1000,$precision);
}


/**
 * @param string $flag 'char'标记 获取字符串   'num' 标记获取数字
 * @param int $num 验证标识的个数
 * @return string
 */
function getVc($flag = '', $num = 0){
    /**获取验证标识**/
    $arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',1,2,3,4,5,6,7,8,9,0);
    $vc = '';
    //字符串
    if($flag == 'char'){
        for($i = 0; $i < $num; $i++){
            $index = rand(0,61);
            $vc .= $arr[$index];
        }
        $vc .= time();
    }elseif($flag == 'num'){  //数字
        for($i = 0; $i < $num; $i++){
            $index = rand(52,61);
            $vc .= $arr[$index];
        }
    }
    return $vc;
}

/**
 * @param $token
 * @return mixed
 * 根据token搜索用户
 */
function searchMember($token){
    if(empty($token)){
        apiResponse('400','请重新登陆');
    }
    $member = M('Member') ->where(array('token'=>$token,'status'=>array('neq',9))) ->find();
    if(!$member){
        apiResponse('300','用户信息有误');
    }
    $res = M('Member') ->where(array('token'=>$token,'status'=>array('neq',9),'expire_time'=>array('egt',time()))) ->find();
    if(!$res){
        apiResponse('400','请重新登录');
    }
    return $member;
}

function dynamic($type, $user_id, $m_id, $object_id){
    $data['type'] = $type;
    $data['user_id'] = $user_id;
    $data['m_id'] = $m_id;
    $data['object_id'] = $object_id;
    $data['create_time'] = time();
    $result = M('Dynamic') ->add($data);
    return $result;
}

function deleteDynamic($a_order_id, $m_id, $message_id){
    $where['a_order_id'] = $a_order_id;
    $where['m_id'] = $m_id;
    $where['id'] = array('neq',$message_id);
    $res = M('Message') ->where($where) ->field('id as message_id, m_id as user_id') ->select();
    foreach($res as $k => $v){
        $whereis['object_id'] = $v['message_id'];
        $whereis['type']      = 3;
        $whereis['user_id']   = $m_id;
        $data['status'] = 9;
        $data['update_time'] = time();
        $result = M('Dynamic') ->where($whereis) ->data($data) ->save();
    }
    return true;
}

/**
 * @package 获得随机CODE
 * @author zhouwei
 * @param $status 1 数字 2 数字大写 3 数字小写 4大写小写 5 数字大小写
 * @param $digit 取几位
 * @return string CODE码
 */
function randomKey($status,$digit){

    switch ($status) {
        case 1  :
            $str  = '1234567890';
            break;
        case 2  :
            $str  = '1234567890';
            $str .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
        case 3  :
            $str  = '1234567890';
            $str .= 'abcdefghijklmnopqrstuvwxyz';
            break;
        case 4 :
            $str  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $str .= 'abcdefghijklmnopqrstuvwxyz';
            break;
        case 5 :
            $str   = '1234567890';
            $str  .= 'abcdefghijklmnopqrstuvwxyz';
            $str  .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
    }
    $disorganize = str_shuffle($str); // 打乱字符
    $fetchNum = substr($disorganize,0,$digit);
    return $fetchNum;
}

function httpPost($url, $data){ // 模拟提交数据函数
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







