<?php
namespace Manager\Model;

/**
 * Class MemberModel
 * @package Manager\Model
 * 会员模型
 */
class MemberModel extends BaseModel {

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
        array('birthday', 'require', '出生年月不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
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
                    ->field('m.*,file.path')
                    ->where($param['where'])
                    ->join(array(
                        'LEFT JOIN '.C('DB_PREFIX').'file file ON file.id = m.head_pic',
                    ))
                    ->find();
        return $row;
    }

    function findVerify($param = array()) {
        $row = $this->alias('m')
            ->field('m.*')
            ->where($param['where'])
            ->find();
        $row['front_pic'] = api('System/getFiles',array($row['front_pic'],array('id','path')));
        $row['hand_pic'] = api('System/getFiles',array($row['hand_pic'],array('id','path')));
        return $row;
    }
}