<?php
namespace Manager\Logic;
/**
 * Class CommentLogic
 * @package Manager\Logic
 * 评价
 */
class CommentLogic extends BaseLogic{

    public function getList($request = array()){
        if($request['record_id']){
            $param['where']['comment.w_r_id'] = $request['record_id'];
            $_SESSION['comment.w_r_id'] = $request['record_id'];
        }else{
            $param['where']['comment.w_r_id'] = $_SESSION['comment.w_r_id'];
        }

        if(!empty($request['member_nickname'])){
            $param['where']['member.nickname'] = array('like','%'.$request['member_nickname'].'%');
        }

        if(!empty($request['user_nickname'])){
            $param['where']['user.nickname'] = array('like','%'.$request['user_nickname'].'%');
        }

        $param['where']['comment.status']   = array('lt',9);        //状态
        $param['order']              = 'comment.number ASC, comment.create_time desc';   //排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('Comment')->getList($param);
        return $result;
    }

    public function findRow($request = array()){
        if(!empty($request['id'])) {
            $param['where']['comment.id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['comment.status'] = array('lt',9);
        $row = D('Comment')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
    /**
     * @param array $request
     * @return bool
     */
    public function examineStatus($request = array()){
        //判断参数
        if(empty($request['model']) || empty($request['ids']) || !isset($request['status'])) {
            $this->setLogicError('参数错误！'); return false;
        }
        //执行前操作
        if(!$this->beforeSetStatus($request)) { return false; }
        //判断是数组ID还是字符ID
        if(is_array($request['ids'])) {
            //数组ID
            $where['id'] = array('in',$request['ids']);
            $ids = implode(',',$request['ids']);
        } elseif (is_numeric($request['ids'])) {
            //数字ID
            $where['id'] = $request['ids'];
            $ids = $request['ids'];
        }
        $data = array(
            'status'        => $request['status'],
            'update_time'   => time()
        );
        $result = D($request['model'])->where($where)->data($data)->save();
        if($result) {
            //行为日志
            api('Manager/ActionLog/actionLog', array('change_status',$request['model'],$ids,AID));
            //执行后操作
            if(!$this->afterSetStatus($result,$request)) { return false; }
            $this->setLogicSuccess('操作成功！'); return true;
        } else {
            $this->setLogicError('操作失败！'); return false;
        }
    }

}