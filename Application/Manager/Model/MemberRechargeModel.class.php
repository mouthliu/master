<?php
namespace Manager\Model;

/**
 * Class RechargeModel
 * @package Manager\Model
 * 提现数据层
 */
class MemberRechargeModel extends BaseModel {


    function getList($param = array()) {
        if(!empty($param['page_size'])) {
            $total      = $this->alias('recharge')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        $model  = $this->alias('recharge')
            ->field('recharge.*,m.nickname')
            ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'member m ON m.id = recharge.m_id',
            ))
            ->order($param['order']);
        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    function findRow($param = array()) { }
}