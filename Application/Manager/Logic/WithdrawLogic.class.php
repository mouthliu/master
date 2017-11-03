<?php
namespace Manager\Logic;

/**
 * Class WithdrawLogic
 * @package Manager\Logic
 * 提现数据层
 */
class WithdrawLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取列表
     */
    function getList($request = array()) {
        if($request['name']){
            $param['where']['bank.name'] = array('like','%'.$request['name'].'%');
        }
        if($request['user_type']){
            $param['where']['withdraw.user_type'] = $request['user_type'];
        }
        if($request['bank_type']){
            $param['where']['bank.bank_type'] = $request['bank_type'];
        }
        if($request['bank_number']){
            $param['where']['bank.bank_number'] = array('like','%'.$request['bank_number'].'%');
        }
        if(!empty($request['start_time']) && !empty($request['end_time'])){
            $start_time = strtotime($request['start_time']);
            $end_time   = strtotime($request['end_time']);
            $param['where']['withdraw.create_time']   = array('between',"$start_time,$end_time");
        }elseif(!empty($request['start_time']) && empty($request['end_time'])){
            $start_time = strtotime($request['start_time']);
            $param['where']['withdraw.create_time']   = array('egt',"$start_time");
        }elseif(empty($request['start_time']) && !empty($request['end_time'])){
            $end_time   = strtotime($request['end_time']);
            $param['where']['withdraw.create_time']   = array('elt',"$end_time");
        }else{

        }

    	$param['where']['withdraw.status']   = array('lt',9);        //状态
        $param['order'] = 'withdraw.create_time DESC';   //排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数
        $result = D('Withdraw')->getList($param);
        return $result;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['withdraw.id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['withdraw.status'] = array('lt',9);
        $row = D('Withdraw')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }

    /**
     * @param array $request
     * @return bool
     */
    public function doWithdraw($request = array()){
        //判断参数
        if(empty($request['model']) || empty($request['ids']) || !isset($request['status'])) {
            $this->setLogicError('参数错误！'); return false;
        }
        //执行前操作
        if(!$this->beforeSetStatus($request)) { return false; }
        //判断是数组ID还是字符ID
        if(is_array($request['ids'])) {
            //数组ID
            $where['id'] = array('in',$request['ids']);
            $ids = implode(',',$request['ids']);
        } elseif (is_numeric($request['ids'])) {
            //数字ID
            $where['id'] = $request['ids'];
            $ids = $request['ids'];
        }
        $data = array(
            'status'        => $request['status'],
            'update_time'   => time()
        );
        $result = D($request['model'])->where($where)->data($data)->save();
        if($result) {
            //行为日志
            api('Manager/ActionLog/actionLog', array('change_status',$request['model'],$ids,AID));
            //执行后操作
            if(!$this->afterSetStatus($result,$request)) { return false; }
            $this->setLogicSuccess('操作成功！'); return true;
        } else {
            $this->setLogicError('操作失败！'); return false;
        }
    }
}