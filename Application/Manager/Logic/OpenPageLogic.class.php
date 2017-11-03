<?php

namespace Manager\Logic;
/**
 * @author zhouwei
 * Class OpenPageLogic
 * @package Manager\Logic 开启页
 */
class OpenPageLogic extends BaseLogic
{

    public function getList($request = array())
    {
        // TODO: Implement getList() method.
        $param['where']['status']   = array('lt',9);        //状态
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('OpenPage')->getList($param);

        return $result;
    }

    public function findRow($request = array())
    {
        // TODO: Implement findRow() method.
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $row = D('OpenPage')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['picture'] = api('System/getFiles',array($row['picture']));
        return $row;
    }
}