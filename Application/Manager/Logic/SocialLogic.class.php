<?php
namespace Manager\Logic;

/**
 * Class OrderLogic
 * @package Manager\Logic
 * 协会逻辑层
 */
class SocialLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取列表
     */
    function getList($request = array()){
        if($request['social_name']){
            $param['where']['social.social_name'] = array('like','%'.$request['social_name'].'%');
        }
        if($request['nickname']){
            $param['where']['master.nickname'] = array('like','%'.$request['nickname'].'%');
        }
        if($request['one_contact']){
            $param['where']['social.one_contact'] = array("like",'%'.$request['one_contact'].'%');
        }
        if($request['two_contact']){
            $param['where']['social.two_contact'] = array('like','%'.$request['two_contact'].'%');
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
        if($request['status'] != 999){
            $param['where']['social.status'] = $request['status'];
        }else{
            $param['where']['social.status'] = 0;
        }
        if(empty($request['status'])){
            $param['where']['social.status'] = array('neq',9);
        }
        $param['order'] = 'social.create_time DESC';   //排序
        $param['page_size'] = C('LIST_ROWS');      //页码
        $param['parameter']         = $request;
        $result = D('Social')->getList($param);
        return $result;
    }
    function getApply($request = array()){
        if($request['nickname']){
            $param['where']['master.nickname'] = array('like','%'.$request['nickname'].'%');
        }
        if(!empty($request['start_time']) && !empty($request['end_time'])){
            $start_time = strtotime($request['start_time']);
            $end_time   = strtotime($request['end_time']);
            $param['where']['s_apply.create_time']   = array('between',"$start_time,$end_time");
        }elseif(!empty($request['start_time']) && empty($request['end_time'])){
            $start_time = strtotime($request['start_time']);
            $param['where']['s_apply.create_time']   = array('egt',"$start_time");
        }elseif(empty($request['start_time']) && !empty($request['end_time'])){
            $end_time   = strtotime($request['end_time']);
            $param['where']['s_apply.create_time']   = array('elt',"$end_time");
        }else{

        }
        if($request['position']){
            if($request['position'] != 999){
                $param['where']['s_apply.position'] = $request['position'];
            }else{
                $param['where']['s_apply.position'] = 0;
            }
        }
        if($request['apply_status']){
            if($request['apply_status'] != 999){
                $param['where']['s_apply.apply_status'] = $request['apply_status'];
            }else{
                $param['where']['s_apply.apply_status'] = 0;
            }
        }
        if($request['social_id']){
            $param['where']['s_apply.social_id'] = $_POST['social_id'];
        }else{
            $param['where']['s_apply.social_id'] = $request['id'];
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
            $param['where']['social.id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $row = D('Social')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        foreach(explode(",",$row['social_pic']) as $k=>$v){
            $where['id'] = $v;
            $pic = D('file')->where($where)->field("path")->find();
            $row['pic'][]= $pic['path'];
        }
        return $row;
    }

    public function findVerify($request = array()){
        if(!empty($request['id'])) {
            $param['where']['social.id'] = $request['id'];
        } else {
            $this->setLogicError('用户ID为空！'); return false;
        }
        $param['where']['social.status'] = array('lt',9);
        $row = D('Social') ->findVerify($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        foreach(explode(",",$row['social_pic']) as $k=>$v){
            $where['id'] = $v;
            $pic = D('file')->where($where)->field("path")->find();
            $row['pic'][]= $pic['path'];
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

    public function mfindVerify($request = array()){
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
}