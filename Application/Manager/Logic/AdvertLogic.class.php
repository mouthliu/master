<?php
namespace Manager\Logic;

/**
 * Class AdvertLogic
 * @package Manager\Logic
 * 广告管理逻辑层
 */
class AdvertLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取列表
     */
    function getList($request = array()) {
        if($request['type']){
            $param['where']['ad.type'] = $request['type'];
        }
        $param['where']['ad.status'] = array('neq',9);
        $param['order'] = 'type DESC, sort DESC, create_time DESC';   //排序
        $param['page_size'] = C('LIST_ROWS');      //页码
        $param['parameter']         = $request;
        $result = D('Advert')->getList($param);
        return $result;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['ad.id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $row = D('Advert')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['ad_pic'] = api('System/getFiles',array($row['ad_pic']));
        return $row;
    }

}