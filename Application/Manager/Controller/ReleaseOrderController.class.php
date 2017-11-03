<?php
namespace Manager\Controller;

/**
 * Class AdvertController
 * @package Manager\Controller
 * 微记类别表控制器
 */
class ReleaseOrderController extends BaseController {
    public function getUpdateRelation()
    {
        $country = M('Country') ->where(array('status'=>array('neq',9))) ->field('id as country_id, country_cn') ->order('letter ASC') ->select();
        $this ->assign('country',$country);
    }

    public function getIndexRelation()
    {
        $country = M('Country') ->where(array('status'=>array('neq',9))) ->field('id as country_id, country_cn') ->order('letter ASC') ->select();
        $this ->assign('country',$country);
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
            $where['r_order.create_time'] = array('between',"$start_time, $end_time");
        }elseif(!empty($_REQUEST['s_time'])){
            $where['r_order.create_time'] = array('egt',strtotime($_REQUEST['s_time']));
        }elseif(!empty($_REQUEST['e_time'])){
            $where['r_order.create_time'] = array('elt',strtotime($_REQUEST['e_time']));
        }else{

        }
        $title=array('ID','订单标题','用户昵称','订单类型','悬赏订单','出发国家','目的国家','出发时间','创建时间');
        $modelKey=array('id','title','nickname','type','reward_type','g_country_cn','d_country_cn','start_time','create_time');
        $model = M('ReleaseOrder') ->alias('r_order')
            ->field('r_order.id, r_order.title, member.nickname, r_order.type, r_order.reward_type, g_country.country_cn as g_country_cn, d_country.country_cn as d_country_cn, r_order.start_time, r_order.create_time')
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = r_order.m_id',
                'LEFT JOIN '.C('DB_PREFIX').'country g_country ON g_country.id = r_order.g_country',
                'LEFT JOIN '.C('DB_PREFIX').'country d_country ON d_country.id = r_order.d_country'
            ))
            ->order('r_order.create_time desc')
            ->select();
        foreach($model as $k =>$v){
            if($v['type'] == 1){
                $model[$k]['type'] = '帮带订单';
            }else{
                $model[$k]['type'] = '求带订单';
            }

            if($v['reward_type'] == 1){
                $model[$k]['reward_type'] = '悬赏订单';
            }else{
                $model[$k]['reward_type'] = '非悬赏订单';
            }
            $model[$k]['create_time'] = date('Y-m-d H:i',$v['create_time']);
            $model[$k]['start_time'] = date('Y-m-d H:i',$v['start_time']);
        }

        $param = array('title'=>'微带发布订单列表'.date('YmdHis',time()));
        D('GetExcel','Service')->createExcel($title,$modelKey,$model,$param);
    }
}
