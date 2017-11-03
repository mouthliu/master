<?php
namespace Manager\Model;
/**
 * Class CommentModel
 * @package Manager\Model
 * 评价
 */
class MemberCouponModel extends BaseModel{


    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array(
        array('get_time', 'time', self::MODEL_INSERT, 'function'),
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
    );
    /**
     * @param array $param  综合条件参数
     * @return array
     */
    function getList($param = array()) {
        if(!empty($param['page_size'])) {
            $total      = $this->alias('member_coupon')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        $model  = $this->alias('member_coupon')
            ->field('member_coupon.*,m.nickname')
            ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'member m ON m.id = member_coupon.m_id'
            ))
            ->order($param['order']);
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

    }

    /**
     * @param array $param  综合条件参数
     * @return array
     */
    function memberGetList($param = array()) {
        if(!empty($param['page_size'])) {
            $total      = $this->alias('m_coupon')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        $model  = $this->alias('m_coupon')
            ->field('m_coupon.*,m.nickname')
            ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'member m ON m.id = m_coupon.m_id'
            ))
            ->order($param['order']);
        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }
}