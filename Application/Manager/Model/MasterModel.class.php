<?php
namespace Manager\Model;

/**
 * Class MasterModel
 * @package Manager\Model
 * 大师模型
 */
class MasterModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('account', 'require', '账号不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('account', 'email', '账号格式不正确', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('account', '/^0?(13[0-9]|15[0-9]|18[0-9]|14[57]|17[0-9])[0-9]{8}$/', '账号格式不正确', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('password', 'require', '密码不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('password', '6', '密码至少6个字符', self::MUST_VALIDATE, 'length', self::MODEL_INSERT),
        array('nickname', 'require', '昵称不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('nickname', '1,15', '昵称长度不能超过15个字符', self::MUST_VALIDATE, 'length', self::MODEL_BOTH),
        array('sex', 'require', '性别不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('province', 'require', '所在省不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('city', 'require', '所在市不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('area', 'require', '所在区不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('lat', 'require', '请填写所在经度', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('lng', 'require', '请填写所在维度', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('introduction', 'require', '请填写大师详情', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );

    function getList($param = array()) {}

    function findRow($param = array()) {
        $row = $this->alias('m')
            ->field('m.*,file.path,social.social_name')
            ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'file file ON file.id = m.head_pic',
                'LEFT JOIN '.C('DB_PREFIX').'social social ON social.id = m.social_id',
            ))
            ->find();
        return $row;
    }

    function findVerify($param = array()) {
        $row = $this->alias('m')
            ->field('m.*')
            ->where($param['where'])
            ->find();
        $row['front_idcard'] = api('System/getFiles',array($row['front_idcard'],array('id','path')));
        $row['back_idcard']  = api('System/getFiles',array($row['back_idcard'],array('id','path')));
        $row['hand_idcard']  = api('System/getFiles',array($row['hand_idcard'],array('id','path')));
        $row['shop_pic']     = api('System/getFiles',array($row['shop_pic'],array('id','path')));
        return $row;
    }
}