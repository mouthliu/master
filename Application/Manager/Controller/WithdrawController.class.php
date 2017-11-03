<?php
namespace Manager\Controller;

/**
 * Class WithdrawController
 * @package Manager\Controller
 * 提现控制器
 */
class WithdrawController extends BaseController {
    /**
     * 修改提现状态
     */
    function doWithdraw() {
        $this->checkRule(self::$rule);
        $Object = D(CONTROLLER_NAME,'Logic');
        $result = $Object->doWithdraw(I('request.'));
        if($result) {
            $this->success($Object->getLogicSuccess());
        } else {
            $this->error($Object->getLogicError());
        }
    }

    public function getIndexRelation()
    {
        $bank_list = M('SupportBank') ->where(array('status'=>array('neq',9))) ->field('id as bank_id, bank_name') ->select();
        $this ->assign('bank_list',$bank_list);
    }

    /**
     * 提现详情
     */
    public function detail(){
        $this->assign('row',D('Withdraw','Logic')->findRow(I('get.')));
        $this->display('detail');
    }

    /**
     * [derive 导出订单]
     */
    function derive()
    {
        $this->checkRule(self::$rule);
        if(!empty($_REQUEST['s_time'])&&!empty($_REQUEST['e_time'])){
            $start_time = strtotime($_REQUEST['s_time']);
            $end_time = strtotime($_REQUEST['e_time']);
            $where['withdraw.create_time'] = array('between',"$start_time, $end_time");
        }elseif(!empty($_REQUEST['s_time'])){
            $where['withdraw.create_time'] = array('egt',strtotime($_REQUEST['s_time']));
        }elseif(!empty($_REQUEST['e_time'])){
            $where['withdraw.create_time'] = array('elt',strtotime($_REQUEST['e_time']));
        }else{

        }
        $title=array('ID','用户昵称','提现类型','银行卡号','银行卡用户名','支付宝账号','支付宝姓名','微信账号','提现金额','提现时间','体现状态');
        $modelKey=array('id','nickname','type','bank_number','name','alipay_account','alipay_name','wxin_account','price','create_time','status');
        $model = M('Withdraw') ->alias('withdraw') ->where($where)
            ->field('withdraw.id ,nickname, withdraw.type, bank_number, bank.name, alipay_account, alipay_name, wxin_account, price, withdraw.create_time, withdraw.status')
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = withdraw.m_id',
                'LEFT JOIN '.C('DB_PREFIX').'bank bank ON bank.id = withdraw.bank_id'
            ))
            ->order('withdraw.create_time desc')
            ->select();
        foreach($model as $k =>$v){
            if($v['type'] == 1){
                $model[$k]['type'] = '支付宝';
            }elseif($v['type'] == 2){
                $model[$k]['type'] = '微信';
            }else{
                $model[$k]['type'] = '银行卡';
            }

            if($v['status'] == 0){
                $model[$k]['status'] = '待处理';
            }else{
                $model[$k]['status'] = '已处理';
            }
            $model[$k]['create_time'] = date('Y-m-d H:i',$v['create_time']);
        }

        $param = array('title'=>'微带提现列表'.date('YmdHis',time()));
        D('GetExcel','Service')->createExcel($title,$modelKey,$model,$param);
    }
}
