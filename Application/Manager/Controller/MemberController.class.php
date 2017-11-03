<?php
namespace Manager\Controller;

/**
 * Class MemberController
 * @package Manager\Controller
 * 会员控制器
 */
class MemberController extends BaseController {

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
        $region = M('Region') ->where(array('parent_id'=>1)) ->field('id as province_id, region_name') ->order('letter asc') ->select();
        $this ->assign('region',$region);
    }

    /**
     * 修改密码
     */
    function rePass() {
        if(!IS_POST) {
            $this->assign('m_id',$_GET['id']);
            $this->display('rePass');
        } else {
            $Object = D('Member', 'Logic');
            $result = $Object->rePass(I('post.'));
            if ($result) {
                $this->success($Object->getLogicSuccess(), Cookie('__forward__'));
            } else {
                $this->error($Object->getLogicError());
            }
        }
    }

    function verify(){
        $this->assign('row',D('Member','Logic')->findVerify(I('request.')));
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
        $model = M('Member') -> where($where) -> data($data)->save();
        if($model){
            if($data['auth_status'] == 1){
                $dat['update_time'] = time();
                $dat['is_authen'] = 1;
                $release = M('ReleaseOrder') ->where(array('m_id'=>$data['id'])) ->data($dat) ->save();
            }
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
            $where['create_time'] = array('between',"$start_time, $end_time");
        }elseif(!empty($_REQUEST['s_time'])){
            $where['create_time'] = array('egt',strtotime($_REQUEST['s_time']));
        }elseif(!empty($_REQUEST['e_time'])){
            $where['create_time'] = array('elt',strtotime($_REQUEST['e_time']));
        }else{

        }
        $where['status'] = array('neq',9);

        $title=array(
            'ID',
            '用户邮箱账号',
            '用户名',
            '所属地区',
            '环信账号',
            '消息推送状态',
            '积分',
            '余额',
            '注册时间',
        );
        $modelKey=array(
            'id',
            'account',
            'nickname',
            'address_info',
            'easemob_account',
            'push_message',
            'integral',
            'balance',
            'create_time',
        );
        $model = M('Member') ->alias('member') ->where($where)
            ->JOIN(array(
                'LEFT JOIN '.C('DB_PREFIX').'region p_region ON p_region.id = member.province',
                'LEFT JOIN '.C('DB_PREFIX').'region c_region ON c_region.id = member.city',
                'LEFT JOIN '.C('DB_PREFIX').'region a_region ON a_region.id = member.area',
            ))
            ->field('member.*,p_region.region_name as province,c_region.region_name as city,a_region.region_name as area')
            ->select();
        foreach($model as $k =>$v){
            $model[$k]['create_time'] = date('Y-m-d H:i',$v['create_time']);
            $model[$k]['address_info'] = $v['province'].$v['city'].$v['area'];
            if($v['push_message'] == 1){
                $model[$k]['push_message'] = '推送消息';
            }else{
                $model[$k]['push_message'] = '不推送消息';
            }

        }
        $param=array('title'=>'大师会员统计'.date('YmdHis',time()));
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
