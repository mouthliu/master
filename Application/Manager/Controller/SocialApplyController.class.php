<?php
namespace Manager\Controller;

/**
 * Class SocialApplyController
 * @package Manager\Controller
 * 成员申请表控制器
 */
class SocialApplyController extends BaseController {
    public function getUpdateRelation()
    {
        $social = M('SocialApply') ->where(array('status'=>array('neq',9))) ->order('create_time ASC') ->select();
        $this ->assign('socialapply',$social);
    }

    public function getIndexRelation()
    {
        $social = M('SocialApply') ->where(array('status'=>array('neq',9))) ->order('create_time ASC') ->select();
        $this ->assign('socialapply',$social);
    }
    function verify(){
        $this->assign('row',D('SocialApply','Logic')->findVerify(I('request.')));
        $this->display();
    }

    /**
     * 申请协会审核
     */
    public function memberStatus()
    {
        if(!$_POST){
            $this->error('参数错误');
        }
        $data = $_POST;
        $where['id'] = $data['id'];
        $data['update_time'] = time();
        $data['accept_time'] = time();
        if($_POST['apply_status'] == 2){
            $data['position'] = 0;
        }
        $model = M('SocialApply') -> where($where) -> data($data)->save();
        if($model){
            $this->success('审核成功',Cookie('__forward__'));
        }else{
            $this->error('审核失败');
        }
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
            $where['social.create_time'] = array('between',"$start_time, $end_time");
        }elseif(!empty($_REQUEST['s_time'])){
            $where['social.create_time'] = array('egt',strtotime($_REQUEST['s_time']));
        }elseif(!empty($_REQUEST['e_time'])){
            $where['social.create_time'] = array('elt',strtotime($_REQUEST['e_time']));
        }else{

        }
        $model = M('Social') ->alias('social')
            ->field('social.*,master.nickname as master_nickname')
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = social.master_id',
            ))
            ->order('social.create_time desc')
            ->select();

        foreach($model as $k =>$v){
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
