<?php
namespace Manager\Model;

/**
 * Class MessageModel
 * @package Manager\Model
 * 信息提现模型
 */
class ReasonModel extends BaseModel {

    protected $_validate = array(
//        array('picture', 'require', '请上传新闻图片', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('ad_url','/http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/','链接格式不正确！',self::VALUE_VALIDATE,'regex'),
        array('reason_name', 'require', '请填写原因名称', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH)
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
            $total      = $this->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        $model  = $this ->where($param['where']) ->order($param['order']);

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
        $row = $this ->where($param['where']) ->find();
        return $row;
    }
}