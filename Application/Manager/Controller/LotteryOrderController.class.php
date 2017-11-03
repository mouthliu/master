<?php
namespace Manager\Controller;

/**
 * Class LotteryOrderController
 * @package Manager\Controller
 * 抽奖订单表控制器
 */
class LotteryOrderController extends BaseController {
    public function getUpdateRelation()
    {
        $order = M('LotteryOrder') ->where(array('status'=>array('neq',9))) ->order('create_time ASC') ->select();
        $this ->assign('LotteryOrder',$order);
    }

    public function getIndexRelation()
    {
        $order = M('LotteryOrder') ->where(array('status'=>array('neq',9))) ->order('create_time ASC') ->select();
        $this ->assign('LotteryOrder',$order);
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
            $where['l_order.create_time'] = array('between',"$start_time, $end_time");
        }elseif(!empty($_REQUEST['s_time'])){
            $where['l_order.create_time'] = array('egt',strtotime($_REQUEST['s_time']));
        }elseif(!empty($_REQUEST['e_time'])){
            $where['l_order.create_time'] = array('elt',strtotime($_REQUEST['e_time']));
        }else{

        }
        $model = M('LotteryOrder') ->alias('l_order')
            ->field('l_order.*,member.nickname,lottery.lo_name')
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = l_order.m_id',
                'LEFT JOIN '.C('DB_PREFIX').'lottery lottery ON lottery.id = l_order.lottery_id'
            ))
            ->order('l_order.create_time desc')
            ->select();
        foreach($model as $k =>$v) {
            $model[$k]['create_time'] = date('Y-m-d H:i', $v['create_time']);
        }
        $title=array(
            'ID',
            '中奖人姓名',
            '中奖名称',
            '消耗积分',
            '联系方式',
            '创建时间'
        );
        $modelKey=array(
            'id',
            'nickname',
            'lo_name',
            'con_inte',
            'phone',
            'create_time'
        );
        $param = array('title'=>'订单列表'.date('YmdHis',time()));
        D('GetExcel','Service')->createExcel($title,$modelKey,$model,$param);
    }
}
