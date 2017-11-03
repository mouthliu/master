<?php
namespace Manager\Model;
/**
 * Class CommentModel
 * @package Manager\Model
 * 评价
 */
class CouponModel extends BaseModel{

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('satisty_price', 'require', '请填写满足金额', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('discount_price', 'require', '请填写优惠金额', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('integral', 'require', '请填写兑换积分'),
        array('integral', 'number', '兑换积分必须是整数'),
        array('start_time', 'require', '开始时间不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('end_time', 'require', '结束时间不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
    );
    /**
     * @param array $param  综合条件参数
     * @return array
     */
    function getList($param = array()) {
        if(!empty($param['page_size'])) {
            $total      = $this ->where($param['where']) ->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        $model = $this ->where($param['where']) ->order($param['order']);
        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    /**
     * @param array $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this->where($param['where'])->find();
        $row['start_time'] = date('Y-m-d',$row['start_time']);
        $row['end_time'] = date('Y-m-d',$row['end_time']);
        return $row;
    }
}