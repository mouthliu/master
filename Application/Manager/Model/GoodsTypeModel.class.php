<?php
namespace Manager\Model;

/**
 * Class MemberModel
 * @package Manager\Model
 * 会员模型
 */
class GoodsTypeModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('type_name', 'require', '商品类别不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );

    function getList($param = array()) {
        if(!empty($param['page_size'])) {
            $total      = $this ->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }

        $model  = $this ->where($param['where']) ->order($param['order']);

        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();

        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    function findRow($param = array()) {
        $row = $this ->where($param['where']) ->find();
        return $row;
    }
}