<?php
namespace Manager\Controller;

/**
 * Class GoodsController
 * @package Manager\Controller
 * 商品控制器
 */
class GoodsController extends BaseController {
    /**
     * [derive 导出订单]
     * @author zhouwei
     * @return [type] [description]
     */
    function derive()
    {
        $this->checkRule(self::$rule);
        if(!empty($_REQUEST['s_time'])&&!empty($_REQUEST['e_time'])){
            $start_time = strtotime($_REQUEST['s_time']);
            $end_time = strtotime($_REQUEST['e_time']);
            $where['goods.create_time'] = array('between',"$start_time, $end_time");
        }elseif(!empty($_REQUEST['s_time'])){
            $where['goods.create_time'] = array('egt',strtotime($_REQUEST['s_time']));
        }elseif(!empty($_REQUEST['e_time'])){
            $where['goods.create_time'] = array('elt',strtotime($_REQUEST['e_time']));
        }else{

        }
        $where['goods.status'] = array('neq',9);

        $title=array(
            'ID',
            '商品名称',
            '商品价格',
            '发布商品大师',
            '商品描述',
            '满意度',
            '兑换积分',
            '运费',
            '商品状态'
        );
        $modelKey=array(
            'id',
            'goods_name',
            'price',
            'nickname',
            'goods_info',
            'degree',
            'integral',
            'freight',
            'status',
        );
        $model = M('Goods') ->alias('goods') ->where($where)
            ->JOIN(array(
                'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = goods.master_id',
                'LEFT JOIN '.C('DB_PREFIX').'goods_type goods_type ON goods_type.id = goods.goods_type',
            ))
            ->field(
                'goods.*,
                master.nickname,
                goods_type.type_name'
            )
            ->select();
        foreach($model as $k =>$v){
            $model[$k]['create_time'] = date('Y-m-d H:i',$v['create_time']);
            if($v['status'] == 0 ){
                $model[$k]['status'] = "禁用";
            }elseif($v['status'] == 1){
                $model[$k]['status'] = "启用";
            }
        }
        $param=array('title'=>'大师商品统计'.date('YmdHis',time()));
        D('GetExcel','Service')->createExcel($title,$modelKey,$model,$param);
    }

    public function getIndexRelation(){
        $first = M('GoodsType') ->where(array('status'=>array('neq',9),'parent_id'=>'0')) ->field('id as parent_id, type_name') ->select();
        $goods_type = M('GoodsType') ->where(array('status'=>array('neq',9))) ->field('id as goods_type_id, type_name') ->select();
        $this ->assign('goods_type',$goods_type);
        $this ->assign('first',$first);
    }

    public function getAddRelation()
    {
        $first = M('GoodsType') ->where(array('status'=>array('neq',9),'parent_id'=>'0')) ->field('id as parent_id, type_name') ->select();
        $goods_type = M('GoodsType') ->where(array('status'=>array('neq',9))) ->field('id as goods_type_id, type_name') ->select();
        $this ->assign('goods_type',$goods_type);
        $this ->assign('first',$first);
    }

    public function getUpdateRelation()
    {
        $first = M('GoodsType') ->where(array('status'=>array('neq',9),'parent_id'=>'0')) ->field('id as parent_id, type_name') ->select();
        $goods_type = M('GoodsType') ->where(array('status'=>array('neq',9))) ->field('id as goods_type_id, type_name') ->select();
        $this ->assign('goods_type',$goods_type);
        $this ->assign('first',$first);
    }

    public function libraryCity(){
        if(!$_POST['parent_id']){
            $this ->error('一级不能为空');
        }
        $area = M('GoodsType') ->where(array('parent_id'=>$_POST['parent_id'])) ->field('id as goods_type_id, type_name') ->select();
        $this->ajaxReturn($area,Json);
    }
    public function common(){
        if(!$_GET['id']){
            $this->error('参数错误!');
        }
        $where['goods_id'] = $_GET['id'];
        $where['o_comment.status'] = array('neq','9');
        $common = M('OrderComment')->alias('o_comment')
            ->where($where)
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = o_comment.m_id',
            ))
            ->field('o_comment.*, member.nickname')
            ->select();
        $this->assign('common',$common);
        $this->display();
    }
}
