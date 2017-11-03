<?php
namespace Manager\Model;

/**
 * Class RewardOrderModel
 * @package Manager\Model
 * 悬赏订单模型
 */
class RewardOrderModel extends BaseModel {

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
            $total      = $this ->alias('reward') ->where($param['where'])
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = reward.m_id',
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = reward.master_id'
                ))
                ->count();
            $Page       = $this ->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page ->show();
        }
        $model  = $this ->alias('reward') ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = reward.m_id',
                'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = reward.master_id'
            ))
            ->field('reward.*,member.nickname,master.nickname as master_nickname')
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
        $row = $this ->alias('reward') ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = reward.m_id',
                'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = reward.master_id',
                'LEFT JOIN '.C('DB_PREFIX').'file file ON file.id = reward.picture',
            ))
            ->field('reward.*, member.nickname as m_nickname,master.nickname as master_nickname,file.path')
            ->find();
        return $row;
    }
}