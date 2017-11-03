<?php
namespace Manager\Model;

/**
 * Class SupportBankModel
 * @package Manager\Model
 * 支持银行模型
 */
class SupportBankModel extends BaseModel {

    protected $_validate = array(
        array('lo_name', 'require', '请填写奖品名称', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('lo_pic', 'require', '请上传奖品图片', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('lo_num', 'require', '请填写奖品数量', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('lo_intergral', 'require', '请输入所需积分', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('lo_percent', 'require', '请输入中奖概率', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
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
            $total      = $this ->alias('s_bank') ->where($param['where'])
                ->count();
            $Page       = $this ->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page ->show();
        }
        $model  = $this ->alias('s_bank') ->where($param['where'])
            ->field('s_bank.*')->order($param['order']);

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
        $row = $this ->alias('s_bank') ->where($param['where'])
            ->field('s_bank.*')->find();
        return $row;
    }
}