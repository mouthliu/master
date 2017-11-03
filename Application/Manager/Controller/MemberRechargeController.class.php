<?php
namespace Manager\Controller;

/**
 * Class RechargeController
 * @package Manager\Controller
 * 充值控制器
 */
class MemberRechargeController extends BaseController {


    /**
     * [derive 导出订单]
     */
    function derive()
    {
        $this->checkRule(self::$rule);
        if(!empty($_REQUEST['s_time'])&&!empty($_REQUEST['e_time'])){
            $start_time = strtotime($_REQUEST['s_time']);
            $end_time = strtotime($_REQUEST['e_time']);
            $where['recharge.create_time'] = array('between',"$start_time, $end_time");
        }elseif(!empty($_REQUEST['s_time'])){
            $where['recharge.create_time'] = array('egt',strtotime($_REQUEST['s_time']));
        }elseif(!empty($_REQUEST['e_time'])){
            $where['recharge.create_time'] = array('elt',strtotime($_REQUEST['e_time']));
        }else{

        }
        $title=array('充值ID','充值订单号','充值用户','充值方式','充值金额','充值时间','状态');
        $modelKey=array('id','order_sn','nickname','type','price','create_time','status');
        $model = M('Recharge') ->alias('recharge') ->where($where)
            ->field('recharge.id, order_sn, nickname, type, price, recharge.create_time, recharge.status')
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = recharge.m_id'
            ))
            ->order('recharge.create_time desc')
            ->select();
        foreach($model as $k =>$v){
            if($v['type'] == 1){
                $model[$k]['type'] = '支付宝充值';
            }elseif($v['type'] == 2){
                $model[$k]['type'] = '微信充值';
            }else{
                $model[$k]['type'] = '银行卡充值';
            }

            if($v['status'] == 0){
                $model[$k]['status'] = '待充值';
            }else{
                $model[$k]['status'] = '已支付';
            }
            $model[$k]['create_time'] = date('Y-m-d H:i',$v['create_time']);
        }

        $param = array('title'=>'微带充值列表'.date('YmdHis',time()));
        D('GetExcel','Service')->createExcel($title,$modelKey,$model,$param);
    }
}
