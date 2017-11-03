<?php
namespace Manager\Model;

/**
 * Class SocialApplyModel
 * @package Manager\Model
 * 成员申请模型
 */
class SocialApplyModel extends BaseModel {

    protected $_validate = array(
//        array('trade_address', 'require', '请填写交易地址', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
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
            $total      = $this ->alias('s_apply') ->where($param['where'])
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = s_apply.master_id',
                    'LEFT JOIN '.C('DB_PREFIX').'social social ON social.id = s_apply.social_id',
                ))
                ->count();
            $Page       = $this ->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page ->show();
        }
        $model  = $this ->alias('s_apply') ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = s_apply.master_id',
                'LEFT JOIN '.C('DB_PREFIX').'social social ON social.id = s_apply.social_id',
            ))
            ->field('s_apply.*,social.social_name,social.social_name,master.nickname as master_nickname')
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
        $row = $this ->alias('s_apply') ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = s_apply.master_id',
                'LEFT JOIN '.C('DB_PREFIX').'social social ON social.id = s_apply.social_id',
            ))
            ->field('s_apply.*,social.social_name,master.nickname as master_nickname')
            ->find();
        return $row;
    }


    function findVerify($param = array()) {
        $row = $this->alias('s_apply')

            ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = s_apply.master_id',
                'LEFT JOIN '.C('DB_PREFIX').'social social ON social.id = s_apply.social_id',
            ))
            ->field('s_apply.*,social.social_name,master.nickname as master_nickname')
            ->find();
        return $row;
    }

}