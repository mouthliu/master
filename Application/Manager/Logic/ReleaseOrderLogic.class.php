<?php
namespace Manager\Logic;

/**
 * Class AdvertLogic
 * @package Manager\Logic
 * 微记标签逻辑层
 */
class ReleaseOrderLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取列表
     */
    function getList($request = array()){
        if($request['title']){
            $param['where']['r_order.title'] = array('like','%'.$request['title'].'%');
        }
        if($request['nickname']){
            $param['where']['member.nickname'] = array('like','%'.$request['nickname'].'%');
        }
        if($request['type']){
            $param['where']['r_order.type'] = $request['type'];
        }
        if($request['reward_type']){
            $param['where']['r_order.reward_type'] = $request['reward_type'];
        }
        if($request['d_country']){
            $param['where']['r_order.d_country'] = $request['d_country'];
        }
        if($request['g_country']){
            $param['where']['r_order.g_country'] = $request['g_country'];
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

        $param['where']['r_order.status'] = array('neq',9);
        $param['order'] = 'r_order.create_time DESC';   //排序
        $param['page_size'] = C('LIST_ROWS');      //页码
        $param['parameter']         = $request;
        $result = D('ReleaseOrder')->getList($param);
        return $result;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()){
        if(!empty($request['id'])) {
            $param['where']['r_order.id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $row = D('ReleaseOrder')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }

        $row['start_time'] = date('Y-m-d', $row['start_time']);
        $row['end_time'] = date('Y-m-d', $row['end_time']);
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