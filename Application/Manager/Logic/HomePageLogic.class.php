<?php
namespace Manager\Logic;

/**
 * Class HomePageLogic
 * @package Manager\Logic
 * 首页类别逻辑层
 */
class HomePageLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取列表
     */
    function getList($request = array()) {
        if($request['type_name']){
            $param['where']['type_name'] = array('like','%'.$request['type_name'].'%');
        }
        $param['order'] = 'sort asc';   //排序
        $param['page_size'] = C('LIST_ROWS');      //页码
        $param['where']['type'] = 1;
        $param['where']['status'] = array('lt',9);
        $param['parameter']         = $request;
        $result = D('MerchantType')->getList($param);
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
        $row = D('MerchantType')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['logo'] = api('System/getFiles',array($row['logo']));
        return $row;
    }

}