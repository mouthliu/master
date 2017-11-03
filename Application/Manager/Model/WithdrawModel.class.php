<?php
namespace Manager\Model;

/**
 * Class WithdrawModel
 * @package Manager\Model
 * 提现模型
 */
class WithdrawModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
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
            $total      = $this->alias('withdraw')->where($param['where'])
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'bank bank ON bank.id = withdraw.bank_id',
                    'LEFT JOIN '.C('DB_PREFIX').'support_bank s_bank ON s_bank.id = bank.bank_type',
                ))
                ->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        $model  = $this->alias('withdraw')
            ->field('withdraw.*, bank.name, bank.bank_number, s_bank.bank_name')
            ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'bank bank ON bank.id = withdraw.bank_id',
                'LEFT JOIN '.C('DB_PREFIX').'support_bank s_bank ON s_bank.id = bank.bank_type',
            ))
            ->order($param['order']);
        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();

        if($list){
            foreach($list as $k => $v){
                if($v['user_type'] == 1){
                    $nickname = M('Member') ->where(array('id'=>$v['user_id'])) ->getField('nickname');
                }else{
                    $nickname = M('Master') ->where(array('id'=>$v['user_id'])) ->getField('nickname');
                }

                $list[$k]['nickname'] = $nickname?$nickname:'大师用户';
            }
        }

        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    function findRow($param = array()) {
        $row = $this->alias('withdraw')
            ->field('withdraw.*, bank.name, bank.bank_number, s_bank.bank_name, bank.telephone')
            ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'bank bank ON bank.id = withdraw.bank_id',
                'LEFT JOIN '.C('DB_PREFIX').'support_bank s_bank ON s_bank.id = bank.bank_type',
            ))
            ->find();

        if($row['user_type'] == 1){
            $nickname = M('Member') ->where(array('id'=>$row['user_id'])) ->getField('nickname');
//            $account = M('Member') ->where(array('id'=>$row['user_id'])) ->getField('account');
        }else{
            $nickname = M('Master') ->where(array('id'=>$row['user_id'])) ->getField('nickname');
//            $account = M('Master') ->where(array('id'=>$row['user_id'])) ->getField('account');
        }
        $row['nickname'] = $nickname;
        return $row;
    }
}