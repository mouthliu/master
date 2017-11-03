<?php
namespace Manager\Model;

/**
 * Class FileModel
 * @package Manager\Model
 * 微记标签模型
 */
class ReleaseOrderModel extends BaseModel {

    protected $_validate = array(
        array('title', 'require', '请填写订单标题', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('type', 'require', '请选择订单类型', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('reward_type', 'require', '请选择悬赏类型', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('g_country', 'require', '请选择出发国家', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('g_city', 'require', '请填写出发城市', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('d_country', 'require', '请选择抵达国家', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('d_city', 'require', '请填写抵达城市', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
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
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = r_order.m_id'
                ))
                ->count();
            $Page       = $this ->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page ->show();
        }
        $model  = $this ->alias('r_order') ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = r_order.m_id',
                'LEFT JOIN '.C('DB_PREFIX').'country g_country ON g_country.id = r_order.g_country',
                'LEFT JOIN '.C('DB_PREFIX').'country d_country ON d_country.id = r_order.d_country'
            ))
            ->field('r_order.*, member.nickname, g_country.country_cn as g_country_cn, d_country.country_cn as d_country_cn')
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
                'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = r_order.m_id'
            ))
            ->field('r_order.*, member.nickname')
            ->find();
        return $row;
    }
}