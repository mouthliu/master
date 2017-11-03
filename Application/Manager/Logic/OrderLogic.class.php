<?php
namespace Manager\Logic;

/**
 * Class OrderLogic
 * @package Manager\Logic
 * 订单逻辑层
 */
class OrderLogic extends BaseLogic {

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

        $param['where']['r_order.status'] = array('neq',9);
        $param['order'] = 'r_order.create_time DESC';   //排序
        $param['page_size'] = C('LIST_ROWS');      //页码
        $param['parameter']         = $request;
        $result = D('Order')->getList($param);
//        dump($result);
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
        $row = D('Order')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['address'] = unserialize($row['address']);
        $row['order_serialization'] = unserialize($row['order_serialization']);
        foreach ($row['order_serialization'] as $key =>$value) {
            $row['order_serialization'] = $value[0];
        }
        switch ($row['order_status']) {
            case 0:$row['order_status'] = "待支付";break;
            case 1:$row['order_status'] = "待发货";break;
            case 2:$row['order_status'] = "待收货";break;
            case 3:$row['order_status'] = "待评价";break;
            case 4:$row['order_status'] = "已完成";break;
            case 5:$row['order_status'] = "已取消";break;
            case 6:$row['order_status'] = "退款中";break;
            case 7:$row['order_status'] = "退款成功";break;
            case 8;$row['order_status'] = "退款失败";break;
            default:$row['order_status'] = "暂无";
        }
        switch($row['pay_type']){
            case 0:$row['pay_type'] = "未支付";break;
            case 1:$row['pay_type'] = "支付宝";break;
            case 2:$row['pay_type'] = "微信";break;
            case 3:$row['pay_type'] = "银行卡";break;
            case 4:$row['pay_type'] = "余额";break;
        }
        $row['start_time'] = date('Y-m-d', $row['start_time']);
        $row['end_time'] = date('Y-m-d', $row['end_time']);

        return $row;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findCustomer($request = array())
    {
        if (!empty($request['id'])) {
            $param['where']['customer.order_id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！');
            return false;
        }
        if(!empty($request['type'])){
            $param['where']['customer.order_type'] = $request['type'];
        }
        //D调用modle M直接调用数据表
        $row = D('Order')->findCustomer($param);
        if (!$row) {
            $this->setLogicError('未查到此记录！');return false;
        }

        foreach(explode(",",$row['picture']) as $k=>$v){
            $where['id'] = $v;
            $pic = D('file')->where($where)->field("path")->find();
            $row['pic']= $pic['path'];
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