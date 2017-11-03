<?php
namespace Manager\Model;

/**
 * Class OrderCommentModel
 * @package Manager\Model
 * 订单评价模型
 */
class OrderCommentModel extends BaseModel {

    protected $_validate = array(
//        array('label_name', 'require', '请填写标签名称', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
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
        if(!empty($param['page_size'])){
            $total = $this->alias('order_c')->where($param['where'])
                ->join(array(
                        'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = order_c.m_id',
                        'LEFT JOIN '.C('DB_PREFIX').'goods goods ON goods.id = order_c.goods_id',
                        'LEFT JOIN '.C('DB_PREFIX').'order o_order ON o_order.id = order_c.order_id',
                    )
                )
                ->count();
            $Page = $this->getPage($total,$param['page_size'],$param['parameter']);
            $Page_show = $Page->show();
        }
        $model = $this->alias('order_c')->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = order_c.m_id',
                'LEFT JOIN '.C('DB_PREFIX').'goods goods ON goods.id = order_c.goods_id',
                'LEFT JOIN '.C('DB_PREFIX').'order o_order ON o_order.id = order_c.order_id',
            ))
            ->field('order_c.*,goods.goods_name as c_goods_name,member.nickname as c_nickname,o_order.order_sn as c_order_sn')
            ->order($param['order']);

        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';
        $list = $model->select();
        return array('list'=>$list,'page'=>!empty($Page_show) ? $Page_show : '');
    }

    /**
     * @param $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this ->where($param['where']) ->find();
        return $row;
    }
}