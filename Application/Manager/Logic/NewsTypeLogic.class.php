<?php
namespace Manager\Logic;
/**
 * Class MessageLogic
 * @package Manager\Logic
 * 新闻数据层
 */
class NewsTypeLogic extends BaseLogic {
    /**
     * @param array $request
     * @return array
     * 获取列表
     */
    function getList($request = array()) {
        if($request['type_name']){
            $param['where']['type_name'] = array('like','%'.$request['type_name'].'%');
        }
        $param['where']['status'] = array('neq',9);
        $param['order'] = 'sort DESC, create_time DESC';   //排序
        $param['page_size'] = C('LIST_ROWS');      //页码
        $param['parameter'] = $request;
        $result = D('NewsType')->getList($param);
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
        $row = D('NewsType')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
}