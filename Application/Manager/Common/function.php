<?php
/**
 * 后台公共文件
 * 主要定义后台公共函数库
 */

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
/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 */
function is_login() {
    $admin = session('admin');
    if (empty($admin)) {
        return 0;
    } else {
        return session('admin_sign') == data_auth_sign($admin) ? $admin['a_id'] : 0;
    }

}

/**
 * @param null $a_id
 * @return boolean true-超管理员，false-非超管理员
 * 是否是超级管理员
 */
function is_administrator($a_id = null) {
    $a_id = is_null($a_id) ? is_login() : $a_id;
    return $a_id && (intval($a_id) === C('USER_ADMINISTRATOR'));
}

/**
 * @param string $type
 * @return mixed
 * 获取属性类型信息
 */
function get_attribute_type($type = '') {
    // TODO 可以加入系统配置
    static $_type = array(
        'num'       =>  array('数字','int(10) UNSIGNED NOT NULL'),
        'string'    =>  array('字符串','varchar(255) NOT NULL'),
        'textarea'  =>  array('文本框','text NOT NULL'),
        'datetime'  =>  array('时间','int(10) NOT NULL'),
        'bool'      =>  array('布尔','tinyint(2) NOT NULL'),
        'select'    =>  array('枚举','char(50) NOT NULL'),
    	'radio'		=>	array('单选','char(10) NOT NULL'),
    	'checkbox'	=>	array('多选','varchar(100) NOT NULL'),
    	'editor'    =>  array('编辑器','text NOT NULL'),
    	'picture'   =>  array('上传图片','int(10) UNSIGNED NOT NULL'),
    	'file'    	=>  array('上传附件','int(10) UNSIGNED NOT NULL'),
    );
    return $type?$_type[$type][0]:$_type;
}

/**
 * 获取对应状态的文字信息
 * @param int $status
 * @return string 状态文字 ，false 未获取到
 */
function get_status_title($status = null) {
    if(!isset($status)) {
        return false;
    }
    switch ($status) {
        case -1 : return    '已删除';   break;
        case 0  : return    '禁用';     break;
        case 1  : return    '正常';     break;
        case 2  : return    '待审核';   break;
        default : return    false;      break;
    }
}

/**
 * 获取性别
 * @param int $status
 * @return string 状态文字 ，false 未获取到
 */
function get_sex($sex = null) {
    if(!isset($sex)) {
        return false;
    }
    switch ($sex) {
        case 0  : return    '待设置';     break;
        case 1  : return    '男';     break;
        case 2  : return    '女';     break;
        default : return    false;      break;
    }
}
/**
 * @param null $status
 * @return bool|string
 * 意见反馈对应状态
 */
function get_feedback_status_title($status = null) {
    if(!isset($status)) {
        return false;
    }
    switch ($status) {
        case 0 : return    '未处理';   break;
        case 1  : return    '已处理';     break;
        default : return    false;      break;
    }
}
/**
 * @param null $status
 * @return bool|string
 * 获取评价状态
 */
function get_comment_status_title($status = null) {
    if(!isset($status)) {
        return false;
    }
    switch ($status) {
        case 0 : return    '未审核';   break;
        case 1  : return    '已审核';     break;
        default : return    false;      break;
    }
}
/**
 * @param null $status
 * @return bool|string
 */
function get_comment_status_name($status = null) {
    if(!isset($status)) {
        return false;
    }
    switch ($status) {
        case 0 : return    '未审核';   break;
        case 1  : return    '已审核';     break;
        default : return    false;      break;
    }
}
/**
 * @param $status
 * @return bool|string
 * 获取数据的状态操作
 */
function show_status_name($status) {
    switch ($status) {
        case 0  : return    '启用';     break;
        case 1  : return    '禁用';     break;
        case 2  : return    '审核';	 break;
        default : return    false;     break;
    }
}

/**
 * @param string $table
 * @return string
 * 获取新闻状态
 */
function get_news_status($status = '') {
    switch ($status) {
        case 0  : return    '未发布';   break;
        case 1  : return    '已发布';   break;
        case 9  : return    '已删除';   break;
        default : return    false;      break;
    }
}

/**
 * @param $status
 * @return bool|string
 * 获取数据的状态操作
 */
function show_service_name($status) {
    switch ($status) {
        case 0  : return    '审核';     break;
        case 1  : return    '取消审核';     break;
        default : return    false;     break;
    }
}

function get_service_title($status = null) {
    if(!isset($status)) {
        return false;
    }
    switch ($status) {
        case 0  : return    '未审核';     break;
        case 1  : return    '已审核';     break;
        default : return    false;      break;
    }
}

/**
 * @param $status
 * @return bool|string
 * 获取数据的状态操作
 */
function show_status_icon($status) {
    switch ($status) {
        case 0  : return    'icon-ok-sign';       break;
        case 1  : return    'icon-minus-sign';    break;
        case 2  : return    '';		       break;
        default : return    false;               break;
    }
}

/**
 * @param string $table
 * @return string
 * 获取表的中文名称
 */
function get_table_name($table = '') {
    switch ($table) {
        case 'Action'           : return    '行为表';       break;
        case 'ActionLog'        : return    '行为日志表';    break;
        case 'Administrator'    : return    '管理员表';    break;
        default                 : return    '';             break;
    }
}


/**
 * @param $status
 * @return string
 * 获取插件状态名称
 */
function get_plugins_status_title($status) {
    switch ($status) {
        case 1       : return    '启用';    break;
        case 9       : return    '损坏';    break;
        case null    : return    '未安装';  break;
        case 0       : return    '禁用';    break;
        default      : return    '';       break;
    }
}

/**
 * @param $value
 * @param $config
 * @return mixed
 * 获取标记对应的数组类型配置信息
 */
function get_config_title($value, $config) {
    $list = C(''.$config.'');
    return empty($list[$value]) ? '' : $list[$value];
}

/**
 * @param $status
 * @return string
 * 获取发送状态
 */
function get_send_status($status) {
    switch ($status) {
        case 0       : return    '失败';    break;
        case 1       : return    '成功';    break;
        default      : return    '';       break;
    }
}

/**
 * @param $string
 * @return array
 * 分析枚举类型配置值 格式 a:名称1,b:名称2
 */
function parse_config_attr($string) {
    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if(strpos($string,':')) {
        $value  =   array();
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k]   = $v;
        }
    } else {
        $value  =   $array;
    }
    return $value;
}

/**
 * @param $string
 * @return array
 * 分析枚举类型字段值 格式 a:名称1,b:名称2
 * 暂时和 parse_config_attr功能相同
 * 但请不要互相使用，后期会调整
 */
function parse_field_attr($string) {
    if(0 === strpos($string,':')) {
        // 采用函数定义
        return   eval(substr($string,1).';');
    }
    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if(strpos($string,':')) {
        $value  =   array();
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k]   = $v;
        }
    } else {
        $value  =   $array;
    }
    return $value;
}

/**
 * @param $str  要执行替换的字符串
 * @param $rep_flag 替换标记
 * @param $tar_str 目标字符
 * @return mixed
 */
function replace($str, $rep_flag, $tar_str) {
    return $str = preg_replace("/{".$rep_flag."}/i", ''.$tar_str.'', $str);
}

/**
 * 创建像这样的查询: "IN('a','b')";
 * @access   public
 * @param    mix      $item_list      列表数组或字符串
 * @param    string   $field_name     字段名称
 * @return   void
 */
function db_create_in($item_list, $field_name = ''){
    if (empty($item_list)) {
        return $field_name . " IN ('') ";
    }
    else {
        if (!is_array($item_list)) {
            $item_list = explode(',', $item_list);
        }
        $item_list = array_unique($item_list);
        $item_list_tmp = '';
        foreach ($item_list AS $item) {
            if ($item !== '') {
                $item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
            }
        }
        if (empty($item_list_tmp)) {
            return $field_name . " IN ('') ";
        }
        else {
            return $field_name . ' IN (' . $item_list_tmp . ') ';
        }
    }
}

/**
 * 	作用：将xml转为array
 */
function xmlToArray($xml){
    //将XML转为array
    $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $array_data;
}

/**
 * [优惠券状态]
 * @author zhouwei
 * @param $param
 */
function couponStatus($param){
    switch ($param) {
        case 0       : return    '未发放';    break;
        case 1       : return    '已发放';    break;
        default      : return    '';       break;
    }
}

/**
 * [审核状态 : 快递员]
 * @author zhouwe
 * @param $param
 */
function deliveryStatus($param){
    switch ($param) {
        case 0       : return    '非配送员';    break;
        case 1       : return    '等待审核';    break;
        case 2       : return    '审核失败';    break;
        case 3       : return    '审核成功';    break;
        default      : return    '';           break;
    }
}

/**
 * [热门搜索词 : 状态]
 * @param $param
 * @return string
 */
function hotWorldStatus($param){
    switch ($param) {
        case 0       : return    '禁用';    break;
        case 1       : return    '启用';    break;
        default      : return    '';           break;
    }
}

/**
 * [首页显示状态]
 * @param $param
 * @return string
 */
function mTypeIsAppShowStatus($param)
{
    switch ($param) {
        case 0       : return    '隐藏';    break;
        case 1       : return    '显示';    break;
        default      : return    '';           break;
    }
}

/**
 * 商品类型状态
 * @param $param
 * @return string
 */
function goodsTypeStatus($param)
{
    switch ($param) {
        case 0       : return    '正常';    break;
        case 9       : return    '商家删除';    break;
        default      : return    '';           break;
    }
}

/**
 * [payType 支付方式]
 * @author zhouwei
 * @param  [type] $type [支付方式ID]
 * @return [type]       [支付方式]
 */
function payType($type)
{
    switch ($type) {
        case '1'    : return    '微信';       break;
        case '2'    : return    '支付宝';   break;
        case '3'    : return    '余额';     break;
        default     : return    false;     break;
    }
}

/**
 * [getDeliveryStatus 天马配送支付状态]
 * @author zhouwei
 * @return [type] [description]
 *  0  待支付  1  待接单  2  待分配  3  待取货  4  待送货  5  配送完成  6  取消订单
 */
 function getDeliveryStatus($status)
 {
    switch ($status) {
        case '0'    : return    '待支付';       break;
        case '1'    : return    '待接单';   break;
        case '2'    : return    '待分配';     break;
        case '3'    : return    '待取货';     break;
        case '4'    : return    '待送货';     break;
        case '5'    : return    '配送完成';     break;
        case '6'    : return    '取消订单';     break;
        default     : return    false;     break;
    }
 }

/**
 * [getDeliveryWay 配送方式]
 * @author zhouwei
 * @return [type] [description]
 */
 function getDeliveryWay($status){
    switch($status){
        case '1'    : return    '单车配送';   break;
        case '2'    : return    '三轮配送';     break;
        default     : return    false;     break;
    }
 }

/**
 * [deliveryOrderType 快递订单类型]
 * @author zhouwei
 * @return [type] [description]
 * 1 天马专送 2 天马配送(在线支付) 3 天马配送(线下支付)
 */
 function deliveryOrderType($status)
 {
    switch($status){
        case '1'    : return    '天马专送';   break;
        case '2'    : return    '天马配送(在线支付)';     break;
        case '3'    : return    '天马配送(线下支付)';     break;
        default     : return    false;     break;
    }
 }