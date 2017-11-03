<?php

namespace Manager\Logic;

/**
 * [统计]
 * @author zhouwei
 * Class StatisLogic
 * @package Manager\Logic
 */
class StatisLogic extends  BaseLogic
{
    public function getList($request = array())
    {
        if($request['type_name']){
            $param['where']['type_name'] = array('like','%'.trim($request['type_name']).'%');
        }
        $param['where']['type'] = 2;
        $param['where']['status'] = array('lt',9);//状态
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('MerchantType')->getList($param);

        return $result;
    }

    public function findRow($request = array())
    {
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }

        $param['where']['status'] = array('lt',9);
        $row = D('MerchantType')->getRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }

}