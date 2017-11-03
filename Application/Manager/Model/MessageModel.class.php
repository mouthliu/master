<?php
namespace Manager\Model;

/**
 * Class MessageModel
 * @package Manager\Model
 * 信息提现模型
 */
class MessageModel extends BaseModel {


    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('headline', 'require', '请填写系统消息大标题', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('headline', '1,30', '标题长度不能超过30个字符', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
        array('type', 'require', '请选择发送类型', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('title',  '1,30', '标题长度不能超过30个字符', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
        array('content', 'require', '请填写标题内容', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),

    );
    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );

    /**
     * @param array $param
     * @return array
     * 获取列表
     */
    function getList($param = array()) {
        if(!empty($param['page_size'])) {
            $total      = M('MessageList')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        $model  = M('MessageList') ->where($param['where']) ->order($param['order']);
        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    function findRow($param = array()) {
        $row = $this->alias('message')
            ->field('message.*,member.nickname')
            ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = message.m_id',
            ))
            ->find();
        return $row;
    }

    /**
     * @param array $param
     * @return array
     * 获取列表
     */
    function message($param = array()) {
        if(!empty($param['page_size'])) {
            $total      = $this ->alias('message') ->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        if($param['where']['message.user_type'] == 1){
            $join ='LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = message.user_id';
            $field = 'message.*,member.nickname';
        }
        if($param['where']['message.user_type'] == 2){
            $join ='LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = message.user_id';
            $field = 'message.*,master.nickname';
        }
        $model  = $this ->alias('message') ->where($param['where'])
            ->join(array(
                $join
            ))
            ->field($field)
            ->order($param['order']);
        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

}