<?php
namespace Manager\Controller;

/**
 * Class MasterController
 * @package Manager\Controller
 * 大师控制器
 */
class MasterController extends BaseController {

    function getUpdateRelation() {
        $region = M('Region') ->where(array('parent_id'=>1)) ->field('id as province_id, region_name') ->order('letter asc') ->select();
        $city   = M('Region') ->where(array('region_type'=>2)) ->field('id as city_id, region_name') ->order('letter asc') ->select();
        $area   = M('Region') ->where(array('region_type'=>3)) ->field('id as area_id, region_name') ->order('letter asc') ->select();
        $this ->assign('region',$region);
        $this ->assign('city',$city);
        $this ->assign('area',$area);
    }

    function getAddRelation() {
        $this->assign("nickname",'大师用户');
        $city = M('Region') ->where(array('region_type'=>2)) ->field('id as city_id, region_name') ->order('letter asc') ->select();
        $this ->assign('city',$city);
    }

    /**
     * 修改密码
     */
    function rePass() {
        if(!IS_POST) {
            $this->assign('master_id',$_GET['id']);
            $this->display('rePass');
        } else {
            $Object = D('Master', 'Logic');
            $result = $Object->rePass(I('post.'));
            if ($result) {
                $this->success($Object->getLogicSuccess(), Cookie('__forward__'));
            } else {
                $this->error($Object->getLogicError());
            }
        }
    }

    function verify(){
        $this->assign('row',D('Master','Logic')->findVerify(I('request.')));
        $this->display();
    }

    /**
     * 用户审核
     */
    public function memberStatus()
    {
        if(!$_POST){
            $this->error('参数错误');
        }
        $data = $_POST;
        $where['id'] = $data['id'];
        $data['update_time'] = time();
        $model = M('Master') -> where($where) -> data($data)->save();
        if($model){
            $this->success('审核成功',Cookie('__forward__'));
        }else{
            $this->error('审核失败');
        }
    }

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
            $where['master.create_time'] = array('between',"$start_time, $end_time");
        }elseif(!empty($_REQUEST['s_time'])){
            $where['master.create_time'] = array('egt',strtotime($_REQUEST['s_time']));
        }elseif(!empty($_REQUEST['e_time'])){
            $where['master.create_time'] = array('elt',strtotime($_REQUEST['e_time']));
        }else{

        }
        $where['master.status'] = array('neq',9);

        $title=array(
            'ID',
            '注册账号',
            '大师昵称',
            '真实姓名',
            '身份证号码',
            '联系方式',
            '所在地区',
            '大师简介',
            '大师评分',
            '余额',
            '所属协会',
            '注册时间',
            '认证状态'
        );
        $modelKey=array(
            'id',
            'account',
            'nickname',
            'name',
            'idcard',
            'phone',
            'address_info',
            'introduction',
            'score',
            'balance',
            'social_name',
            'create_time',
            'auth_status'
        );
        $model = M('Master') ->alias('master') ->where(array('master.status'=>array('neq',9)))
            ->JOIN(array(
                'LEFT JOIN '.C('DB_PREFIX').'region p_region ON p_region.id = master.province',
                'LEFT JOIN '.C('DB_PREFIX').'region c_region ON c_region.id = master.city',
                'LEFT JOIN '.C('DB_PREFIX').'region a_region ON a_region.id = master.area',
                'LEFT JOIN '.C('DB_PREFIX').'social social ON social.id = master.social_id',
            ))
            ->field(
                        'master.*,
                        social.social_name,
                        p_region.region_name as province,
                        c_region.region_name as city,
                        a_region.region_name as area'
                    )
            ->select();
        foreach($model as $k =>$v){
            $model[$k]['create_time'] = date('Y-m-d H:i',$v['create_time']);
            $model[$k]['address_info'] = $v['province'].$v['city'].$v['area'];
            if($v['auth_status'] == 0){
                $model[$k]['auth_status'] ="未认证";
            }elseif($v['auth_status'] == 1){
                $model[$k]['auth_status'] ="认证失败";
            }elseif($v['auth_status'] == 2){
                $model[$k]['auth_status'] ="认证中";
            }elseif($v['auth_status'] == 3){
                $model[$k]['auth_status'] ="认证成功";
            }
        }
        $param=array('title'=>'大师列表统计'.date('YmdHis',time()));
        D('GetExcel','Service')->createExcel($title,$modelKey,$model,$param);
    }

    public function libraryCity(){
        if(!$_POST['parent_id']){
            $this ->error('父级地址不能为空');
        }
        if(!$_POST['type']){
            $this ->error('类别不能为空');
        }
        if($_POST['type'] == 1){
            $city = M('Region') ->where(array('parent_id'=>$_POST['parent_id'])) ->field('id as city_id, region_name') ->select();
            $this->ajaxReturn($city,Json);
        }else{
            $area = M('Region') ->where(array('parent_id'=>$_POST['parent_id'])) ->field('id as area_id, region_name') ->select();
            $this->ajaxReturn($area,Json);
        }
    }
}
