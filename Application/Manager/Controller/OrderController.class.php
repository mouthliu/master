<?php
namespace Manager\Controller;

/**
 * Class OrderController
 * @package Manager\Controller
 * 订单表控制器
 */
class OrderController extends BaseController {
    public function getUpdateRelation()
    {
        $order = M('Order') ->where(array('status'=>array('neq',9))) ->order('create_time ASC') ->select();
        $this ->assign('order',$order);
    }

    public function getIndexRelation()
    {
        $order = M('Order') ->where(array('status'=>array('neq',9))) ->order('create_time ASC') ->select();
        $this ->assign('order',$order);
    }

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
            $where['r_order.create_time'] = array('between',"$start_time, $end_time");
        }elseif(!empty($_REQUEST['s_time'])){
            $where['r_order.create_time'] = array('egt',strtotime($_REQUEST['s_time']));
        }elseif(!empty($_REQUEST['e_time'])){
            $where['r_order.create_time'] = array('elt',strtotime($_REQUEST['e_time']));
        }else{

        }
        $model = M('Order') ->alias('r_order')
            ->field('r_order.*,member.nickname as m_nickname,master.nickname as master_nickname')
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = r_order.m_id',
                'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = r_order.master_id',
            ))
            ->order('r_order.create_time desc')
            ->select();

        foreach($model as $k =>$v){
            $model[$k]['name'] = unserialize($v['address'])['name'];
            $model[$k]['telephone']=unserialize($v['address'])['telephone'];
            $model[$k]['address_info']=unserialize($v['address'])['city'].unserialize($v['address'])['area'].unserialize($v['address'])['address_info'];
            $goods_list = unserialize($v['order_serialization']);
            foreach($goods_list['goods'] as $kk=>$vv){
                $model[$k]['goods_name'] = $vv['goodsDetail']['goods_name'];
                $model[$k]['goods_type'] = $vv['goodsDetail']['goods_type'];
            }
            $model[$k]['c_time'] = date('Y-m-d H:i',$v['create_time']);
        }
        $title=array(
            'ID',
            '订单编号',
            '订单总价',
            '用户昵称',
            '联系方式',
            '联系地址',
            '大师名称',
            '商品名称',
            '商品类型',
            '运费',
            '创建时间'
        );
        $modelKey=array(
            'id',
            'order_sn',
            'total_price',
            'name',
            'telephone',
            'address_info',
            'master_nickname',
            'goods_name',
            'goods_type',
            'freight',
            'c_time'
        );
        $param = array('title'=>'订单列表'.date('YmdHis',time()));
        D('GetExcel','Service')->createExcel($title,$modelKey,$model,$param);
    }
}
