<?php
namespace Manager\Model;

/**
 * Class FileModel
 * @package Manager\Model
 * 微记标签模型
 */
class AddOrderModel extends BaseModel {

    protected $_validate = array(
//        array('title', 'require', '请填写订单标题', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('type', 'require', '请选择订单类型', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('reward_type', 'require', '请选择悬赏类型', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('g_country', 'require', '请选择出发国家', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('g_city', 'require', '请填写出发城市', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('d_country', 'require', '请选择抵达国家', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('d_city', 'require', '请填写抵达城市', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('start_time', 'require', '请选择开始时间', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('trade_address', 'require', '请填写交易地址', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array (
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );

    /**
     * @param array $param  综合条件参数
     * @return array
     */
    function getList($param = array()) {
        if(!empty($param['page_size'])) {
            $total      = $this ->alias('a_order') ->where($param['where'])
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = a_order.m_id',
                    'LEFT JOIN '.C('DB_PREFIX').'release_order r_order ON r_order.id = a_order.r_order_id'
                ))
                ->count();
            $Page       = $this ->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page ->show();
        }
        $model  = $this ->alias('a_order') ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = a_order.m_id',
                'LEFT JOIN '.C('DB_PREFIX').'release_order r_order ON r_order.id = a_order.r_order_id'
            ))
            ->field('a_order.*, member.nickname,r_order.title')
            ->order($param['order']);

        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();

        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    /**
     * @param $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this ->alias('a_order') ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = a_order.m_id',
                'LEFT JOIN '.C('DB_PREFIX').'release_order r_order ON r_order.id = a_order.r_order_id',
                'LEFT JOIN '.C('DB_PREFIX').'member r_member ON r_member.id = r_order.m_id',
                'LEFT JOIN '.C('DB_PREFIX').'address address ON address.id = a_order.address_id',
                'LEFT JOIN '.C('DB_PREFIX').'country g_country ON g_country.id = r_order.g_country',
                'LEFT JOIN '.C('DB_PREFIX').'country d_country ON d_country.id = r_order.d_country'
            ))
            ->field('a_order.*, member.nickname, r_member.nickname as username, r_order.title, r_order.type as r_type, address.name, address.telephone, address.address_info, g_country.country_cn as g_country_cn, d_country.country_cn as d_country_cn, r_order.g_city, r_order.d_city')
            ->find();

        if($row['r_type'] == 1){
            $row['type_name'] = '帮带订单';
        }else{
            $row['type_name'] = '求带订单';
        }

        if($row['reward_type'] == 1){
            $row['reward_name'] = '悬赏订单';
        }else{
            $row['reward_name'] = '非悬赏订单';
        }

        $coupon = M('MemberCoupon') ->where(array('id'=>$row['coupon'])) ->find();
        $row['coupon_price'] = $coupon?$coupon['price']:'0.00';
        $row['pick_record'] = api('System/getFiles',array($row['pick_record'],array('id','path')));
        $row['receive_record'] = api('System/getFiles',array($row['receive_record'],array('id','path')));
        return $row;
    }
}