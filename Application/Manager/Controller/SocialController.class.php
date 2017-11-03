<?php
namespace Manager\Controller;

/**
 * Class SocialController
 * @package Manager\Controller
 * 协会表控制器
 */
class SocialController extends BaseController {
    public function getUpdateRelation()
    {
        $social = M('Social') ->where(array('status'=>array('neq',9))) ->order('create_time ASC') ->select();
        $this ->assign('social',$social);
    }

    public function getIndexRelation()
    {
        $social = M('Social') ->where(array('status'=>array('neq',9))) ->order('create_time ASC') ->select();
        $this ->assign('social',$social);
    }
    function verify(){
        $this->assign('row',D('Social','Logic')->findVerify(I('request.')));
        $this->display();
    }
    function mverify(){
        $this->assign('row',D('Social','Logic')->mfindVerify(I('request.')));
        $this->display();
    }
    /**
     * 协会审核
     */
    public function memberStatus()
    {
        if(!$_POST){
            $this->error('参数错误');
        }
        $data = $_POST;
        $where['id'] = $data['id'];
        $data['update_time'] = time();
        $model = M('Social') -> where($where) -> data($data)->save();
        unset($where);
        unset($data);
        if($_POST['status'] == 1){
            $where['id'] = $_POST['master_id'];
            $data['social_id'] = $_POST['id'];
            $model1 = M('Master') -> where($where) -> data($data)->save();
            unset($where);
            unset($data);
            $where['social_id'] = $_POST['id'];
            $where['master_id'] = $_POST['master_id'];
            $where['type'] = 1;
            $data['update_time'] = time();
            $data['accept_time'] = time();
            $data['apply_status'] =1;
            $data['position'] =1;
            $data['type'] =1;
            $model2 = M('SocialApply') -> where($where) -> data($data)->save();
        }

        if($model){
            $this->success('审核成功',Cookie('__forward__'));
        }else{
            $this->error('审核失败');
        }
    }
    /**
     * 协会审核
     */
    public function mmemberStatus()
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
        if(empty($_POST['apply_status'])){
            $data['position'] = 0;
        }
        $model = M('SocialApply') -> where($where) -> data($data)->save();
        if($_POST['apply_status'] == 1){
            unset($where);
            unset($data);
            $where['id'] = $_POST['master_id'];
            $data['social_id'] = $_POST['social_id'];
            $model1 = M('Master') -> where($where) -> data($data)->save();
            unset($where);
            unset($data);
            $where['status'] = array("neq","9");
            $where['master_id'] = $_POST['master_id'];
            $where['apply_status'] = array('neq',"1");
            $data['status'] = 9;
            $model2 = M('SocialApply') -> where($where) -> data($data)->save();
        }
        if($model){
            $this->success('审核成功',Cookie('__forward__'));
//              $this->redirect(U("Social/apply"));
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
//    public function apply(){
//        if(!$_GET['id']){
//            $this->error('参数错误!');
//        }
//        $where['s_apply.social_id'] = $_GET['id'];
//        $where['s_apply.status'] = array('neq','9');
//        $apply = M('SocialApply')->alias('s_apply')
//            ->where($where)
//            ->join(array(
//                'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = s_apply.master_id',
//                'LEFT JOIN '.C('DB_PREFIX').'social social ON social.id = s_apply.social_id',
//            ))
//            ->field('s_apply.*,master.nickname as master_nickname,social.social_name')
//            ->select();
//        Cookie('__forward__',$_SERVER['REQUEST_URI']);
//        $this->assign('apply',$apply);
//        $this->display();
//    }

}
