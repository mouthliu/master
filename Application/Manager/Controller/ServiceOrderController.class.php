<?php
namespace Manager\Controller;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/29
 * Time: 9:30
 */
class ServiceOrderController extends BaseController{
    /**
     * [derive 导出订单]
     */
    function derive()
    {
        $this->checkRule(self::$rule);
        echo "<meta charset='utf-8'>";
        if(!empty($_REQUEST['s_time'])&&!empty($_REQUEST['e_time'])){
            $start_time = strtotime($_REQUEST['s_time']);
            $end_time = strtotime($_REQUEST['e_time']);
            $where['s_order.create_time'] = array('between',"$start_time, $end_time");
        }elseif(!empty($_REQUEST['s_time'])){
            $where['s_order.create_time'] = array('egt',strtotime($_REQUEST['s_time']));
        }elseif(!empty($_REQUEST['e_time'])){
            $where['s_order.create_time'] = array('elt',strtotime($_REQUEST['e_time']));
        }else{

        }
        $model = M('ServiceOrder') ->alias('s_order')
            ->field('s_order.*,member.nickname as m_nickname,master.nickname as master_nickname,master_service.service_id,master_service.price as m_s_price,service.title,service.content as service_content')
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = s_order.m_id',
                'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = s_order.master_id',
                'LEFT JOIN '.C('DB_PREFIX').'master_service master_service ON master_service.id = s_order.m_s_id',
                'LEFT JOIN '.C('DB_PREFIX').'service service ON service.id = master_service.service_id'
            ))
            ->order('s_order.create_time desc')
            ->select();
        foreach($model as $k=>$v){
            $model[$k]['c_time'] = date("Y-m-d H:i",$v['create_time']);
        }
        $title=array(
            'ID',
            '订单编号',
            '订单类型',
            '订单内容',
            '订单总价',
            '详情描述',
            '用户姓名',
            '大师名称',
            '创建时间'
        );
        $modelKey=array(
            'id',
            'order_sn',
            'title',
            'service_content',
            'price',
            'content',
            'name',
            'master_nickname',
            'c_time'
        );
        $param = array('title'=>'服务订单列表'.date('YmdHis',time()));
        D('GetExcel','Service')->createExcel($title,$modelKey,$model,$param);
    }


}