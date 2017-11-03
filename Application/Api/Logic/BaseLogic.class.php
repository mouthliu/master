<?php
namespace Api\Logic;
/**
 * Class BaseLogic
 * @package Api\Logic
 * 逻辑层基类
 */
require_once("./JPush/JPush.php");
class BaseLogic{
    protected $member_key = 'b383964f611254900164cb12';
    protected $member_secret = '739c3a3815ec5fde9630dadc';
    protected $master_key = 'f009b33c56f017133309d8a1';
    protected $master_secret = 'a742d056029ac47a4f31d662 ';

    /**
     * 初始化
     */
    public function _initialize(){

    }
    /**
     * 获取配置文件
     */
    public function getConfig(){
        $config = D('Config')->parseList();
        return $config;
    }
    /**
     * @param $m_id
     * @return int
     * 获取用户未读信息条数
     */
    public function getUnReadMessageNum($m_id){
        $where['m_id']   = $m_id;
        $where['status'] = array('eq',0);
        $num = M('Message')->where($where)->count();
        $num = $num?$num:0;
        return $num;
    }
    /**
     * @param int $num
     * @return array
     * 环信注册
     */
    public function easemobRegister(){
        $i = 0;
        while($i < 8){
            $i = $i+1;
            //生成环信账号
            $username = time().rand(00001,99999);
            $res = M('Member')->where(array('easemob_account'=>$username))->count();
            if($res>0){
                continue;
            }
            //生成环信密码
            $password = time();

            //调用环信注册账号
            $register_res = D('Easemob','Service')->createUser(array('username'=>$username,'password'=>$password));
            //判断环信注册结果，如果注册失败，继续调用循环,如果成功直接跳出循环
            if($register_res['error']){
                continue;
            }else{
                break;
            }
        }
        //如果循环8次依旧没有得到想要的结果，返回error,如果成功返回success
        if($register_res['error']){
            return array('flag' => 'error');
        }else{
            return array('flag' => 'success', 'easemob_account' => $username, 'easemob_password' => $password);
        }
    }
    /**
     * 获取百度地址返回
     * @param $data
     * @return mixed
     */
    public function getAddress($data)
    {
        $ak = 'EFa3o1aWFce9qO041CNjNYG6b4H7qcX1';
        $coor = 'bd09ll'; // coor=bd09ll时，返回为百度经纬度坐标
        $ip = $data;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://api.map.baidu.com/location/ip?ak=" . $ak . "&ip=" . $ip . "&coor=" . $coor);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $res = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($res, true);
        if ($json['status'] == 0) {
            // 当存在地址
            $return['register_city'] = $json['content']['address_detail']['city'];
            $return['register_province'] = $json['content']['address_detail']['province'];
        } else {
            // 当不存在地址
            $return['register_city'] = '未知';
            $return['register_province'] = '未知';
        }
        return $return;
    }

    /**
     * @param int $total 总数
     * @param int $page_size 每页记录数
     * @param array $parameter 拼接参数
     * @return \Think\Page 分页对象
     */
    protected function getPage($total = 0, $page_size = 0, $parameter = array()) {
        $Page = new \Think\Page($total, $page_size, $parameter);
        //分页样式配置
        if($total>$page_size) {
            $Page->setConfig('theme','%UP_PAGE% %FIRST% %LINK_PAGE% %END% %DOWN_PAGE% %HEADER%');
        }
        return $Page;
    }

    /**
     * 单条查询或者多条查询
     */
    public function easyMysql($model, $type, $where = '', $data = '', $field = '*', $order = '', $page = '', $limit = ''){
        if($type == 1 && $data != ''){
            //type == 1  新增
            $result = M($model) ->add($data);
        }

        if($type == 2 && $data != ''){
            //type == 2  修改
            $result = M($model) ->where($where) ->data($data) ->save();
        }

        if($type == 3){
            //type == 3  find查询
            $result = M($model) ->where($where) ->field($field) ->order($order) ->find();
        }

        if($type == 4){
            //type == 4  多条查询
            if($page == ''){
                $result = M($model) ->where($where) ->field($field) ->order($order) ->limit($limit) ->select();
            }else{
                $result = M($model) ->where($where) ->field($field) ->order($order) ->page($page, 10) ->select();
            }
        }

        if($type == 5){
            $result = M($model) ->where($where) ->getField($field);
        }

        if($type == 6){
            $result = M($model) ->where($where) ->count();
        }

        return $result;
    }

    /**
     * @param $token
     * @return mixed
     * 根据token搜索用户
     */
    function searchMember($token){
        if(empty($token)){
            apiResponse('-1','请重新登陆');
        }
        $member = M('Member') ->where(array('token'=>$token)) ->find();
        if(!$member){
            apiResponse('0','用户信息有误');
        }
        if($member['status'] == 9){
            apiResponse('0','用户信息有误');
        }
        if($member['status'] == 0){
            apiResponse('0','您暂时无法登陆');
        }

        $res = M('Member') ->where(array('token'=>$token,'status'=>array('neq',9),'expire_time'=>array('egt',time()))) ->find();
        if(!$res){
            apiResponse('-1','请重新登录');
        }
        return $member;
    }

    /**
     * @param $token
     * @return mixed
     * 根据token搜索用户
     */
    function searchMaster($token){
        if(empty($token)){
            apiResponse('-1','请重新登陆');
        }
        $member = M('Master') ->where(array('token'=>$token)) ->find();
        if(!$member){
            apiResponse('0','大师信息有误');
        }
        if($member['status'] == 9){
            apiResponse('0','大师信息有误');
        }
        if($member['status'] == 0){
            apiResponse('0','您暂时无法登陆');
        }

        $res = M('Master') ->where(array('token'=>$token,'status'=>array('neq',9),'expire_time'=>array('egt',time()))) ->find();
        if(!$res){
            apiResponse('-1','请重新登录');
        }
        return $member;
    }



    function searchPhoto($id){
        $path = $this ->easyMysql('File','5',array('id'=>$id),'','path');
        $picture = $path?C('API_URL').$path:'';
        return $picture;
    }

    function setType($model = '', $where = '', $field = '', $num = '', $type = ''){
        if($type == 1){
            $result = M($model) ->where($where) ->setInc($field, $num);
        }else{
            $result = M($model) ->where($where) ->setDec($field, $num);
        }

        return $result;
    }

    public function getFieldSth($model = '', $where = array(), $field = ''){
        $result = M($model) ->where($where) ->getField($field);
        if(!$result){
            apiResponse('0','查询某个字段失败');
        }
        return $result;
    }

    public function findFields($field){
        $field_list = explode(',',$field);
        $field_info = array();
        foreach($field_list as $key =>$val){
            $field_name = $this ->easyMysql('Field',3,array('id'=>$val,'status'=>array('neq',9)),'','id as field_id, field_name');
            if(!empty($field_name)){
                $field_info[] = $field_name;
            }
        }
        return $field_info;
    }

    public function getOrderNum($goods_id){
        $where['_string'] = " ( goods_id = ".$goods_id.") OR ( goods_id like '%,".$goods_id.",%') OR ( goods_id like '%,".$goods_id."') OR ( goods_id like '".$goods_id.",%' )";
        $where['order_status'] = array('in','1,2,3,4,6,7');
        $order_num = M('Order') ->where($where) ->count();
        if(!$order_num){
            $order_num = '0';
        }
        return $order_num;
    }

    public function serviceOrderNum($master_id){
        $where = array('master_id'=>$master_id);
        $order_num = M('ServiceOrder') ->where($where) ->count();
        if(!$order_num){
            $order_num = '0';
        }
        return $order_num;
    }

    function format_time($time){
        //当前时间
        $now = time();
        //传递时间与当前时间相差的秒数
        $diff = $now - $time;
        switch ($time) {
            case $diff < 60:
                $str = '刚刚';
                break;
            case $diff < 3600:
                $str = floor($diff / 60) .'分钟前';
                break;
            case $diff < (3600 * 24):
                $str = floor($diff / 3600) .'小时前';
                break;
            case $time < strtotime('+10 day',$time):
                $str = floor(($now - $time) / 86400).'天前';
                break;
            default:
                $str = date('Y-m-d H:i:s',$time);
                break;
        }
        return $str;
    }

    public function addMessage($user_type, $user_id, $type, $object_id, $headline, $content){
        $data['user_type'] = $user_type;
        $data['user_id']   = $user_id;
        $data['type']      = $type;
        $data['object_id'] = $object_id;
        $data['headline']  = $headline;
        $data['content']   = $content;
        $data['create_time'] = time();
        $message = $this ->easyMysql('Message','1','',$data);

        return $message;
    }

    /**
     * @param $user_type  用户类型  1  用户  2  大师
     * @param $user_id    对应用户ID
     * @param $type       操作类型  1  充值  2  提现
     * @param $title      详情标题
     * @param $symbol     金额正负号  1  正好  2  符号
     * @param $price      操作金额
     * @return mixed
     * 添加详情
     */
    function addDetail($user_type, $user_id, $type, $title, $symbol, $price){
        $data['user_type']   = $user_type;
        $data['user_id']     = $user_id;
        $data['type']        = $type;
        $data['title']       = $title;
        $data['symbol']      = $symbol;
        $data['price']       = $price;
        $data['create_time'] = time();
        $result = M('Detail')->data($data)->add();
        return $result;
    }

    /**
     * @param $user_type
     * @param $user_id
     * @param $type
     * @param $title
     * @param $symbol
     * @param $price
     * @return mixed
     */
    function addIntegral($m_id, $type, $title, $symbol, $integral){
        $data['m_id']        = $m_id;
        $data['type']        = $type;
        $data['title']       = $title;
        $data['symbol']      = $symbol;
        $data['integral']    = $integral;
        $data['create_time'] = time();
        $result = M('Integral')->data($data)->add();
        return $result;
    }

    function addPayLog($m_id, $master_id, $type, $title, $price){
        $data['m_id']        = $m_id;
        $data['master_id']   = $master_id;
        $data['type']        = $type;
        $data['title']       = $title;
        $data['price']       = $price;
        $data['date']        = date('Y-m');
        $data['create_time'] = time();
        $result = M('PayLog')->data($data)->add();
        return $result;
    }

    /**
     * 商品价格和服务价格处理
     */
    function goodsPrice($price, $type){
        $goods_offer = M('Config') ->where(array('id'=>94)) ->getField('value');
        $service_offer = M('Config') ->where(array('id'=>95)) ->getField('value');
        $res = $type==1? $price * (100+$goods_offer) / 1000: $price * (100+$service_offer) / 1000;
        $result = floor($price)>=10 ?floor($res).'9.00':floor($price).'.00';
        return $result;
    }

    /**集成包
     * @param $merchant_id
     * @param $order_id
     * 极光推送
     * 上面引入这个类文件
     */
    public function jPushToMember($member_id,$order_id,$title,$alert){
        //调用极光推送
        $tag[] = ''.$member_id;
        $alias = ''.$member_id;
        $extra['order_id']     = ''.$order_id;
        $this->pushMember($tag,$alias,$title,$alert,$extra);
    }

    public function pushMember($tag,$alias,$title,$alert,$extra){
        $client = new \JPush($this->member_key,$this->member_secret);
        $result = $client->push()
            ->setPlatform(array('ios', 'android'))
            ->addAlias($alias)
            ->addTag($tag)
            ->setNotificationAlert($alert)
            ->addAndroidNotification($alert, $title, 1, $extra)
            ->addIosNotification($alert, 'iOS sound', \JPush::DISABLE_BADGE, true, 'iOS category', $extra)
            ->setOptions(100000, 3600, null, false)
            //true是生产环境   false是开发环境
            ->send();
    }

    /**集成包
     * @param $merchant_id
     * @param $order_id
     * 极光推送
     * 上面引入这个类文件
     */
    public function jPushToMaster($member_id,$order_id,$title,$alert){
        //调用极光推送
        $tag[] = ''.$member_id;
        $alias = ''.$member_id;
        $extra['order_id']     = ''.$order_id;
        $this->pushMember($tag,$alias,$title,$alert,$extra);
    }

    public function pushMaster($tag,$alias,$title,$alert,$extra){
        $client = new \JPush($this->master_key,$this->master_secret);
        $result = $client->push()
            ->setPlatform(array('ios', 'android'))
            ->addAlias($alias)
            ->addTag($tag)
            ->setNotificationAlert($alert)
            ->addAndroidNotification($alert, $title, 1, $extra)
            ->addIosNotification($alert, 'iOS sound', \JPush::DISABLE_BADGE, true, 'iOS category', $extra)
            ->setOptions(100000, 3600, null, false)
            //true是生产环境   false是开发环境
            ->send();
    }

    //2017年09月20日17:57:33
    //zhousl以下是吧日期转换为农历+属性
    var $MIN_YEAR = 1891;
    var $MAX_YEAR = 2100;
    var $lunarInfo = array(
        array(0,2,9,21936),array(6,1,30,9656),array(0,2,17,9584),array(0,2,6,21168),array(5,1,26,43344),array(0,2,13,59728),
        array(0,2,2,27296),array(3,1,22,44368),array(0,2,10,43856),array(8,1,30,19304),array(0,2,19,19168),array(0,2,8,42352),
        array(5,1,29,21096),array(0,2,16,53856),array(0,2,4,55632),array(4,1,25,27304),array(0,2,13,22176),array(0,2,2,39632),
        array(2,1,22,19176),array(0,2,10,19168),array(6,1,30,42200),array(0,2,18,42192),array(0,2,6,53840),array(5,1,26,54568),
        array(0,2,14,46400),array(0,2,3,54944),array(2,1,23,38608),array(0,2,11,38320),array(7,2,1,18872),array(0,2,20,18800),
        array(0,2,8,42160),array(5,1,28,45656),array(0,2,16,27216),array(0,2,5,27968),array(4,1,24,44456),array(0,2,13,11104),
        array(0,2,2,38256),array(2,1,23,18808),array(0,2,10,18800),array(6,1,30,25776),array(0,2,17,54432),array(0,2,6,59984),
        array(5,1,26,27976),array(0,2,14,23248),array(0,2,4,11104),array(3,1,24,37744),array(0,2,11,37600),array(7,1,31,51560),
        array(0,2,19,51536),array(0,2,8,54432),array(6,1,27,55888),array(0,2,15,46416),array(0,2,5,22176),array(4,1,25,43736),
        array(0,2,13,9680),array(0,2,2,37584),array(2,1,22,51544),array(0,2,10,43344),array(7,1,29,46248),array(0,2,17,27808),
        array(0,2,6,46416),array(5,1,27,21928),array(0,2,14,19872),array(0,2,3,42416),array(3,1,24,21176),array(0,2,12,21168),
        array(8,1,31,43344),array(0,2,18,59728),array(0,2,8,27296),array(6,1,28,44368),array(0,2,15,43856),array(0,2,5,19296),
        array(4,1,25,42352),array(0,2,13,42352),array(0,2,2,21088),array(3,1,21,59696),array(0,2,9,55632),array(7,1,30,23208),
        array(0,2,17,22176),array(0,2,6,38608),array(5,1,27,19176),array(0,2,15,19152),array(0,2,3,42192),array(4,1,23,53864),
        array(0,2,11,53840),array(8,1,31,54568),array(0,2,18,46400),array(0,2,7,46752),array(6,1,28,38608),array(0,2,16,38320),
        array(0,2,5,18864),array(4,1,25,42168),array(0,2,13,42160),array(10,2,2,45656),array(0,2,20,27216),array(0,2,9,27968),
        array(6,1,29,44448),array(0,2,17,43872),array(0,2,6,38256),array(5,1,27,18808),array(0,2,15,18800),array(0,2,4,25776),
        array(3,1,23,27216),array(0,2,10,59984),array(8,1,31,27432),array(0,2,19,23232),array(0,2,7,43872),array(5,1,28,37736),
        array(0,2,16,37600),array(0,2,5,51552),array(4,1,24,54440),array(0,2,12,54432),array(0,2,1,55888),array(2,1,22,23208),
        array(0,2,9,22176),array(7,1,29,43736),array(0,2,18,9680),array(0,2,7,37584),array(5,1,26,51544),array(0,2,14,43344),
        array(0,2,3,46240),array(4,1,23,46416),array(0,2,10,44368),array(9,1,31,21928),array(0,2,19,19360),array(0,2,8,42416),
        array(6,1,28,21176),array(0,2,16,21168),array(0,2,5,43312),array(4,1,25,29864),array(0,2,12,27296),array(0,2,1,44368),
        array(2,1,22,19880),array(0,2,10,19296),array(6,1,29,42352),array(0,2,17,42208),array(0,2,6,53856),array(5,1,26,59696),
        array(0,2,13,54576),array(0,2,3,23200),array(3,1,23,27472),array(0,2,11,38608),array(11,1,31,19176),array(0,2,19,19152),
        array(0,2,8,42192),array(6,1,28,53848),array(0,2,15,53840),array(0,2,4,54560),array(5,1,24,55968),array(0,2,12,46496),
        array(0,2,1,22224),array(2,1,22,19160),array(0,2,10,18864),array(7,1,30,42168),array(0,2,17,42160),array(0,2,6,43600),
        array(5,1,26,46376),array(0,2,14,27936),array(0,2,2,44448),array(3,1,23,21936),array(0,2,11,37744),array(8,2,1,18808),
        array(0,2,19,18800),array(0,2,8,25776),array(6,1,28,27216),array(0,2,15,59984),array(0,2,4,27424),array(4,1,24,43872),
        array(0,2,12,43744),array(0,2,2,37600),array(3,1,21,51568),array(0,2,9,51552),array(7,1,29,54440),array(0,2,17,54432),
        array(0,2,5,55888),array(5,1,26,23208),array(0,2,14,22176),array(0,2,3,42704),array(4,1,23,21224),array(0,2,11,21200),
        array(8,1,31,43352),array(0,2,19,43344),array(0,2,7,46240),array(6,1,27,46416),array(0,2,15,44368),array(0,2,5,21920),
        array(4,1,24,42448),array(0,2,12,42416),array(0,2,2,21168),array(3,1,22,43320),array(0,2,9,26928),array(7,1,29,29336),
        array(0,2,17,27296),array(0,2,6,44368),array(5,1,26,19880),array(0,2,14,19296),array(0,2,3,42352),array(4,1,24,21104),
        array(0,2,10,53856),array(8,1,30,59696),array(0,2,18,54560),array(0,2,7,55968),array(6,1,27,27472),array(0,2,15,22224),
        array(0,2,5,19168),array(4,1,25,42216),array(0,2,12,42192),array(0,2,1,53584),array(2,1,21,55592),array(0,2,9,54560)
    );
    /**
     * 将阳历转换为阴历
     * @param year 公历-年
     * @param month 公历-月
     * @param date 公历-日
     */
    function convertSolarToLunar($year,$month,$date){
        //debugger;
        $yearData = $this->lunarInfo[$year-$this->MIN_YEAR];
        if($year==$this->MIN_YEAR&&$month<=2&&$date<=9){
            return array(1891,'正月','初一','辛卯',1,1,'兔');
        }
        return $this->getLunarByBetween($year,$this->getDaysBetweenSolar($year,$month,$date,$yearData[1],$yearData[2]));
    }

    function convertSolarMonthToLunar($year,$month,$date) {
        $yearData = $this->lunarInfo[$year-$this->MIN_YEAR];
        if($year==$this->MIN_YEAR&&$month<=2&&$date<=9){
            return array(1891,'正月','初一','辛卯',1,1,'兔');
        }
        $month_days_ary = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        $dd = $month_days_ary[$month];
        if($this->isLeapYear($year) && $month == 2) $dd++;
        $lunar_ary = array();
        for ($i = 1; $i < $dd; $i++) {
            $array = $this->getLunarByBetween($year,$this->getDaysBetweenSolar($year, $month, $i, $yearData[1], $yearData[2]));
            $array[] = $year . '-' . $month . '-' . $i;
            $lunar_ary[$i] = $array;
        }
        return $lunar_ary;
    }
    /**
     * 将阴历转换为阳历
     * @param year 阴历-年
     * @param month 阴历-月，闰月处理：例如如果当年闰五月，那么第二个五月就传六月，相当于阴历有13个月，只是有的时候第13个月的天数为0
     * @param date 阴历-日
     */
    function convertLunarToSolar($year,$month,$date){
        $yearData = $this->lunarInfo[$year-$this->MIN_YEAR];
        $between = $this->getDaysBetweenLunar($year,$month,$date);
        $res = mktime(0,0,0,$yearData[1],$yearData[2],$year);
        $res = date('Y-m-d', $res+$between*24*60*60);
        $day    = explode('-', $res);
        $year    = $day[0];
        $month= $day[1];
        $day    = $day[2];
        return array($year, $month, $day);
    }
    /**
     * 判断是否是闰年
     * @param year
     */
    function isLeapYear($year){
        return (($year%4==0 && $year%100 !=0) || ($year%400==0));
    }
    /**
     * 获取干支纪年
     * @param year
     */
    function getLunarYearName($year){
        $sky = array('庚','辛','壬','癸','甲','乙','丙','丁','戊','己');
        $earth = array('申','酉','戌','亥','子','丑','寅','卯','辰','巳','午','未');
        $year = $year.'';
        return $sky[$year{3}].$earth[$year%12];
    }
    /**
     * 根据阴历年获取生肖
     * @param year 阴历年
     */
    function getYearZodiac($year){
        $zodiac = array('猴','鸡','狗','猪','鼠','牛','虎','兔','龙','蛇','马','羊');
        return $zodiac[$year%12];
    }
    /**
     * 获取阳历月份的天数
     * @param year 阳历-年
     * @param month 阳历-月
     */
    function getSolarMonthDays($year,$month){
        $monthHash = array('1'=>31,'2'=>$this->isLeapYear($year)?29:28,'3'=>31,'4'=>30,'5'=>31,'6'=>30,'7'=>31,'8'=>31,'9'=>30,'10'=>31,'11'=>30,'12'=>31);
        return $monthHash["$month"];
    }
    /**
     * 获取阴历月份的天数
     * @param year 阴历-年
     * @param month 阴历-月，从一月开始
     */
    function getLunarMonthDays($year,$month){
        $monthData = $this->getLunarMonths($year);
        return $monthData[$month-1];
    }
    /**
     * 获取阴历每月的天数的数组
     * @param year
     */
    function getLunarMonths($year){
        $yearData = $this->lunarInfo[$year - $this->MIN_YEAR];
        $leapMonth = $yearData[0];
        $bit = decbin($yearData[3]);
        for ($i = 0; $i < strlen($bit);$i ++) {
            $bitArray[$i] = substr($bit, $i, 1);
        }
        for($k=0,$klen=16-count($bitArray);$k<$klen;$k++){
            array_unshift($bitArray, '0');
        }
        $bitArray = array_slice($bitArray,0,($leapMonth==0?12:13));
        for($i=0; $i<count($bitArray); $i++){
            $bitArray[$i] = $bitArray[$i] + 29;
        }
        return $bitArray;
    }
    /**
     * 获取农历每年的天数
     * @param year 农历年份
     */
    function getLunarYearDays($year){
        $yearData = $this->lunarInfo[$year-$this->MIN_YEAR];
        $monthArray = $this->getLunarYearMonths($year);
        $len = count($monthArray);
        return ($monthArray[$len-1]==0?$monthArray[$len-2]:$monthArray[$len-1]);
    }
    function getLunarYearMonths($year){
        //debugger;
        $monthData = $this->getLunarMonths($year);
        $res=array();
        $temp=0;
        $yearData = $this->lunarInfo[$year-$this->MIN_YEAR];
        $len = ($yearData[0]==0?12:13);
        for($i=0;$i<$len;$i++){
            $temp=0;
            for($j=0;$j<=$i;$j++){
                $temp+=$monthData[$j];
            }
            array_push($res, $temp);
        }
        return $res;
    }
    /**
     * 获取闰月
     * @param year 阴历年份
     */
    function getLeapMonth($year){
        $yearData = $this->lunarInfo[$year-$this->MIN_YEAR];
        return $yearData[0];
    }
    /**
     * 计算阴历日期与正月初一相隔的天数
     * @param year
     * @param month
     * @param date
     */
    function getDaysBetweenLunar($year,$month,$date){
        $yearMonth = $this->getLunarMonths($year);
        $res=0;
        for($i=1;$i<$month;$i++){
            $res +=$yearMonth[$i-1];
        }
        $res+=$date-1;
        return $res;
    }
    /**
     * 计算2个阳历日期之间的天数
     * @param year 阳历年
     * @param cmonth
     * @param cdate
     * @param dmonth 阴历正月对应的阳历月份
     * @param ddate 阴历初一对应的阳历天数
     */
    function getDaysBetweenSolar($year,$cmonth,$cdate,$dmonth,$ddate){
        $a = mktime(0,0,0,$cmonth,$cdate,$year);
        $b = mktime(0,0,0,$dmonth,$ddate,$year);
        return ceil(($a-$b)/24/3600);
    }
    /**
     * 根据距离正月初一的天数计算阴历日期
     * @param year 阳历年
     * @param between 天数
     */
    function getLunarByBetween($year,$between){
        //debugger;
        $lunarArray = array();
        $yearMonth=array();
        $t=0;
        $e=0;
        $leapMonth=0;
        $m='';
        if($between==0){
            array_push($lunarArray, $year,'正月','初一');
            $t = 1;
            $e = 1;
        }else{
            $year = $between>0? $year : ($year-1);
            $yearMonth = $this->getLunarYearMonths($year);
            $leapMonth = $this->getLeapMonth($year);
            $between = $between>0?$between : ($this->getLunarYearDays($year)+$between);
            for($i=0;$i<13;$i++){
                if($between==$yearMonth[$i]){
                    $t=$i+2;
                    $e=1;
                    break;
                }else if($between<$yearMonth[$i]){
                    $t=$i+1;
                    $e=$between-(empty($yearMonth[$i-1])?0:$yearMonth[$i-1])+1;
                    break;
                }
            }
            $m = ($leapMonth!=0&&$t==$leapMonth+1)?('闰'.$this->getCapitalNum($t- 1,true)):$this->getCapitalNum(($leapMonth!=0&&$leapMonth+1<$t?($t-1):$t),true);
            array_push($lunarArray,$year,$m,$this->getCapitalNum($e,false));
        }
        array_push($lunarArray,$this->getLunarYearName($year));// 天干地支
        array_push($lunarArray,$t,$e);
        array_push($lunarArray,$this->getYearZodiac($year));// 12生肖
        array_push($lunarArray,$leapMonth);// 闰几月
        return $lunarArray;
    }
    /**
     * 获取数字的阴历叫法
     * @param num 数字
     * @param isMonth 是否是月份的数字
     */
    function getCapitalNum($num,$isMonth){
        $isMonth = $isMonth || false;
        $dateHash=array('0'=>'','1'=>'一','2'=>'二','3'=>'三','4'=>'四','5'=>'五','6'=>'六','7'=>'七','8'=>'八','9'=>'九','10'=>'十 ');
        $monthHash=array('0'=>'','1'=>'正月','2'=>'二月','3'=>'三月','4'=>'四月','5'=>'五月','6'=>'六月','7'=>'七月','8'=>'八月','9'=>'九月','10'=>'十月','11'=>'冬月','12'=>'腊月');
        $res='';
        if($isMonth){
            $res = $monthHash[$num];
        }else{
            if($num<=10){
                $res = '初'.$dateHash[$num];
            }else if($num>10&&$num<20){
                $res = '十'.$dateHash[$num-10];
            }else if($num==20){
                $res = "二十";
            }else if($num>20&&$num<30){
                $res = "廿".$dateHash[$num-20];
            }else if($num==30){
                $res = "三十";
            }
        }
        return $res;
    }
    //zhousl以上是吧日期转换为农历+属性 end
    //END
}