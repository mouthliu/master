<?php
namespace Manager\Logic;

/**
 * Class SocialApplyLogic
 * @package Manager\Logic
 * 成员申请逻辑层
 */
class SocialApplyLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取列表
     */
    function getList($request = array()){
        if($request['order_sn']){
            $param['where']['r_order.order_sn'] = array('eq',$request['order_sn']);
        }
        if($request['nickname']){
            $param['where']['member.nickname'] = array('like','%'.$request['nickname'].'%');
        }
        if($request['delivery_sn']){
            $param['where']['r_order.delivery_sn'] = array("eq",$request['delivery_sn']);
        }
        if($request['order_status']){
            $param['where']['order_status'] = array('eq',$request['order_status']);
        }
        if($request['order_status'] == 99){
            $param['where']['order_status'] =array('eq',0);
        }
        if($request['pay_type']){
            $param['where']['pay_type'] = array('eq',$request['pay_type']);
        }
        if($request['pay_type'] == 999){
            $param['where']['pay_type'] =array('eq',0);
        }

        if(!empty($request['start_time']) && !empty($request['end_time'])){
            $start_time = strtotime($request['start_time']);
            $end_time   = strtotime($request['end_time']);
            $param['where']['r_order.create_time']   = array('between',"$start_time,$end_time");
        }elseif(!empty($request['start_time']) && empty($request['end_time'])){
            $start_time = strtotime($request['start_time']);
            $param['where']['r_order.create_time']   = array('egt',"$start_time");
        }elseif(empty($request['start_time']) && !empty($request['end_time'])){
            $end_time   = strtotime($request['end_time']);
            $param['where']['r_order.create_time']   = array('elt',"$end_time");
        }else{

        }
        $param['where']['s_apply.status'] = array('neq',9);
        $param['order'] = 's_apply.create_time DESC';   //排序
        $param['page_size'] = C('LIST_ROWS');      //页码
        $param['parameter']         = $request;
        $result = D('SocialApply')->getList($param);
        return $result;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()){
        if(!empty($request['id'])) {
            $param['where']['s_apply.id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $row = D('SocialApply')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        foreach(explode(",",$row['social_pic']) as $k=>$v){
            $where['id'] = $v;
            $pic = D('file')->where($where)->field("path")->find();
            $row['pic'][]= $pic['path'];
        }
//        dump($row);
        return $row;
    }

    public function findVerify($request = array()){
        if(!empty($request['id'])) {
            $param['where']['s_apply.id'] = $request['id'];
        } else {
            $this->setLogicError('用户ID为空！'); return false;
        }
        $param['where']['s_apply.status'] = array('lt',9);
        $row = D('SocialApply') ->findVerify($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
    /**
     * @param array $request
     * @return bool|mixed
     * 新增 或 修改
     */
    function update($request = array()) {
        //执行前操作
        if(!$this->beforeUpdate($request)) { return false; }
        $model = $request['model'];
        unset($request['model']);
        //获取数据对象
        $data = D($model)->create($request);
        if(!$data) {
            $this->setLogicError(D($model)->getError()); return false;
        }
        //处理数据
        $data = $this->processData($data);
        //判断增加还是修改
        if(empty($data['id'])) {
            //新增数据
            if($_POST['start_time']){
                $data['start_time'] = strtotime($_POST['start_time']);
            }

            if($_POST['end_time']){
                $data['end_time'] = strtotime($_POST['end_time']);
            }
            $data['create_time'] = time();
            $result = D($model)->data($data)->add();
            if(!$result) {
                $this->setLogicError('新增时出错！'); return false;
            }
            //行为日志
            api('Manager/ActionLog/actionLog', array('add',$model,$result,AID));
        } else {
            //创建修改参数
            $where['id'] = $request['id'];
            $data['start_time'] = strtotime($_POST['start_time']);
            $data['end_time'] = strtotime($_POST['end_time']) + 86399;
            $data['update_time'] = time();
            $result = D($model)->where($where)->data($data)->save();
            if(!$result) {
                $this->setLogicError('您未修改任何值！'); return false;
            }
            //行为日志
            api('Manager/ActionLog/actionLog', array('edit',$model,$data['id'],AID));
        }
        //执行后操作
        if(!$this->afterUpdate($result,$request)) { return false; }

        $this->setLogicSuccess($data['id'] ? '更新成功！' : '新增成功！'); return true;
    }
}