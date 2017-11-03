<?php
namespace Api\Model;
use Think\Model;

/**
 * Class BaseModel
 * @package Manager\Model
 * 用户模块类
 */
class BankModel extends BaseModel {
    function getList($param = array()){

    }
    function findRow($param = array()){

    }

    function selectBank($where = array(), $field = '*',$order = ''){
        $bank = M('Bank') ->alias('bank') ->where($where)
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'support_bank support_bank ON support_bank.id = bank.bank_type',
            ))
            ->field($field) ->order($order) ->select();

        return $bank;
    }
}