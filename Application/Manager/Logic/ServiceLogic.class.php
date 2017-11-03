<?php
namespace Manager\Logic;

/**
 * Class AdvertLogic
 * @package Manager\Logic
 * 服务类型逻辑层
 */
class ServiceLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取列表
     */
    function getList($request = array()) {
        if($request['title']){
            $param['where']['service.title'] = array('like','%'.$request['title'].'%');
        }
        $param['where']['service.status'] = array('neq',9);
        $param['order'] = 'service.create_time DESC';   //排序
        $param['page_size'] = C('LIST_ROWS');      //页码
        $param['parameter'] = $request;
        $result = D('Service')->getList($param);
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
        $row = D('Service')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['picture'] = api('System/getFiles',array($row['picture']));
        return $row;
    }

}