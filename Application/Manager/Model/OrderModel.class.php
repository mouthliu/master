<?php
namespace Manager\Model;

/**
 * Class OrderModel
 * @package Manager\Model
 * 订单模型
 */
class OrderModel extends BaseModel {

    protected $_validate = array(
        array('title', 'require', '请填写订单标题', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('type', 'require', '请选择订单类型', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('reward_type', 'require', '请选择悬赏类型', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('start_time', 'require', '请选择开始时间', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('trade_address', 'require', '请填写交易地址', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
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
            $total      = $this ->alias('r_order') ->where($param['where'])
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = r_order.m_id',
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = r_order.master_id'
                ))
                ->count();
            $Page       = $this ->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page ->show();
        }
        $model  = $this ->alias('r_order') ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = r_order.m_id',
                'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = r_order.master_id'
            ))
            ->field('r_order.*,member.nickname,master.nickname as master_nickname')
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
        $row = $this ->alias('r_order') ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = r_order.m_id',
                'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = r_order.master_id',
            ))
            ->field('r_order.*, member.nickname as m_nickname,master.nickname as master_nickname')
            ->find();
        return $row;
    }

    function findCustomer($param = array()){
        $row = M('Customer') ->alias('customer') ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = customer.m_id',
                'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = customer.master_id',
                'LEFT JOIN '.C('DB_PREFIX').'order o_order ON o_order.id = customer.order_id',
                'LEFT JOIN '.C('DB_PREFIX').'reason reason ON reason.id = customer.reason',
                'LEFT JOIN '.C('DB_PREFIX').'delivery_company d_company ON d_company.id = customer.delivery',
            ))
            ->field('customer.*,member.nickname as member_nickname,master.nickname as master_nickname,o_order.order_sn,reason.reason_name,d_company.company_name')
            ->find();
        return $row;
    }
}