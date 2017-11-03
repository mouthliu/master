<?php

namespace Manager\Model;
/**
 * @author zhouwei
 * Class OpenPageModel
 * @package Manager\Model 开启页
 */
class OpenPageModel extends BaseModel
{
    protected $_validate = array();

    protected $_auto=array();

    public function getList($param = array())
    {
        // TODO: Implement getList() method.
        if(!empty($param['page_size'])) {
            $total      = $this->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }

        $model  = $this->where($param['where'])->order($param['order']);

        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();
        // 遍历
        foreach($list as $key => $value){
            $list[$key]['picture'] = M('file') -> where(array('id'=>$value['picture'])) -> getField('path');
        }
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }


    public function findRow($param = array())
    {
        // TODO: Implement findRow() method.
        $row = $this->where($param['where'])->find();
        return $row;
    }
}