<?php
namespace Manager\Logic;
/**
 * Class MessageLogic
 * @package Manager\Logic
 * 退货原因数据层
 */
class ReasonLogic extends BaseLogic {
    /**
     * @param array $request
     * @return array
     * 获取列表
     */
    function getList($request = array()) {
        if($request['reason_name']){
            $param['where']['reason_name'] = array('like','%'.$request['reason_name'].'%');
        }
        $param['where']['status'] = array('neq',9);
        $param['order'] = 'sort DESC, create_time DESC';   //排序
        $param['page_size'] = C('LIST_ROWS');      //页码
        $param['parameter'] = $request;
        $result = D('Reason')->getList($param);
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
        $row = D('Reason')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
}