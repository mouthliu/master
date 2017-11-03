<?php
namespace Manager\Logic;

/**
 * Class CouponLogic
 * @package Manager\Logic
 * 优惠券数据层
 */
class CouponLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取列表
     */
    function getList($request = array()) {
        if(!empty($request['start_time']) & !empty($request['end_time'])){
            $start_time = strtotime($request['start_time']);
            $end_time   = strtotime($request['end_time']);
            $param['where']['start_time']   = array('egt',$start_time);
            $param['where']['end_time']   = array('elt',$end_time);
        }elseif(!empty($request['start_time']) && empty($request['end_time'])){
            $start_time = strtotime($request['start_time']);
            $param['where']['start_time']   = array('egt',"$start_time");
        }elseif(empty($request['start_time']) && !empty($request['end_time'])){
            $end_time   = strtotime($request['end_time']);
            $param['where']['end_time']   = array('elt',"$end_time");
        }else{

        }
    	$param['where']['status']   = array('lt',9);        //状态
        $param['order']              = 'create_time DESC';  //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;              //拼接参数
        $result = D('Coupon')->getList($param);

        return $result;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $row = D('Coupon')->findRow($param);
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
    function grant($request = array()) {
        //执行前操作
        if(!$this->beforeUpdate($request)) { return false; }
        $model = $request['model'];
        unset($request['model']);
        //判断增加还是修改
        if(empty($request['id'])) {
            $this->setLogicError('参数有误！'); return false;
        } else {
            //创建修改参数
            $where['id'] = $request['id'];
            $where['status'] = array('neq',9);
            $res = D($model)->where($where) ->find();
            if(!$res) {
                $this->setLogicError('优惠券信息有误！'); return false;
            }
            $data['status'] = 1;
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

        $this->setLogicSuccess($request['id'] ? '发布成功！' : '发布成功！'); return true;
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
            $data['start_time'] = strtotime($request['start_time']);
            $data['end_time'] = strtotime($request['end_time']) + 86399;
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