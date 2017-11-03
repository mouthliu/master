<?php
namespace Manager\Model;

/**
 * Class FeedbackModel
 * @package Manager\Model
 * 意见反馈模型
 */
class FeedbackModel extends BaseModel {


	
    function getList($param = array()) {
    	if(!empty($param['page_size'])) {
            $total      = $this->alias('feed')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }

        $model  = $this ->alias('feed') ->where($param['where']) ->order($param['order']);

        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();

        foreach($list as $k => $v){
            if($v['user_type'] == 1){
                $member = $this ->easyMysql('Member','3',array('id'=>$v['user_id']),'','account, nickname');
            }elseif($v['user_type'] == 2){
                $member = $this ->easyMysql('Master','3',array('id'=>$v['user_id']),'','account, nickname');
            }
            $list[$k]['nickname'] = $member['nickname']?$member['nickname']:'';
            $list[$k]['account'] = $member['account']?$member['account']:'';
        }

        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    function findRow($param = array()) {
    }
}