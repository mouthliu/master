<?php
namespace Manager\Logic;

/**
 * Class FeedbackLogic
 * @package Manager\Logic
 * 意见反馈逻辑层
 */
class FeedbackLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取列表
     */
    function getList($request = array()) {
//        if($request['account']){
//            $map['member.account'] = array('like','%'.$request['account'].'%');
//            $map['master.account'] = array('like','%'.$request['account'].'%');
//            $map['_logic'] = 'or';
//            $param['where']['_complex'] = $map;
//        }
//        if($request['nickname']){
//            $res['member.nickname'] = array('like','%'.$request['nickname'].'%');
//            $res['master.nickname'] = array('like','%'.$request['nickname'].'%');
//            $res['_logic'] = 'or';
//            $param['where']['_complex'] = $res;
//        }
        if($request['user_type']){
            $param['where']['feed.user_type'] = $request['user_type'];
        }
        if($request['type']){
            $param['where']['feed.type'] = $request['type'];
        }
        if($request['telephone']){
            $param['where']['feed.telephone'] = array('like','%'.$request['telephone'].'%');
        }
    	$param['where']['feed.status']   = array('lt',9);        //状态
    	//根据需要查询审核通过和未审核过的评论
    	if(isset($request['status']) && $request['status'] != '') {
        	$param['where']['feed.status']  = $request['feed.status'];
        }
		$param['order'] = 'feed.create_time desc';
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数
        
        $result = D('Feedback')->getList($param);

        return $result;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()) {
    }

}