<?php
namespace Manager\Logic;

/**
 * Class MessageLogic
 * @package Manager\Logic
 * 信息数据层
 */
class MessageLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取列表
     */
    function getList($request = array()) {
        if($request['headline']){
            $param['where']['headline'] = array('like','%'.$request['headline'].'%');
        }
        $param['where']['status']   = array('lt',9);        //状态
        $param['order']              = 'create_time DESC';   //排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('Message')->getList($param);
        return $result;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['message.id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['message.status'] = array('lt',9);
        $row = D('Message')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }

    /**
     * @param array $request
     * @return bool|mixed
     * 新增 或 修改
     */
    function update($request = array()) {
        //执行前操作
        if(!$this->beforeUpdate($request)) { return false; }
        $model = $request['model'];
        unset($request['model']);
        //获取数据对象
        $data = D($model)->create($request);
        if(!$data) {
            $this->setLogicError(D($model)->getError()); return false;
        }
        //处理数据
        $data = $this->processData($data);
        //判断增加还是修改
        if(empty($data['id'])) {
            //新增数据
            $res = M('MessageList') ->add($data);
            if(!$res){
                $this->setLogicError('列表新增出错！'); return false;
            }
//            $m_id = M('Member') ->select();
//            foreach($m_id as $k => $v){
//                $data['message_id'] = $res;
//                $data['m_id'] = $v['id'];
//                $data['type'] = 1;
//                $result = M('Message')->data($data)->add();
//                if(!$result) {
//                    $this->setLogicError('新增时出错！'); return false;
//                }
//                $dat['type'] = 3;
//                $dat['user_id'] = $v['id'];
//                $dat['object_id'] = $result;
//                $dat['create_time'] = time();
//                $dynamic = M('Dynamic') ->add($dat);
//            }

            //行为日志
            api('Manager/ActionLog/actionLog', array('add',$model,$result,AID));
        } else {
            //创建修改参数
            $where['id'] = $request['id'];
            $result = D($model)->where($where)->data($data)->save();
            if(!$result) {
                $this->setLogicError('您未修改任何值！'); return false;
            }
            //行为日志
            api('Manager/ActionLog/actionLog', array('edit',$model,$data['id'],AID));
        }
        //执行后操作
        if(!$this->afterUpdate($result,$request)) { return false; }

        $this->setLogicSuccess($data['id'] ? '更新成功！' : '新增成功！'); return true;
    }

    public function processData($data = array()){
        $data['content'] = $_POST['content'];
        return $data;
    }

    /**
     * @param array $request
     * @return array
     * 获取列表
     */
    function message($request = array()) {
        if($request['message_id']){
            $param['where']['message.object_id'] = $request['message_id'];
            $_SESSION['message_id'] = $request['message_id'];
        }else{
            $param['where']['message.id'] = $_SESSION['message_id'];
        }

        if($request['type']){
            $param['where']['message.user_type'] = $request['type'];
        }

        if($request['nickname']){
            $param['where']['nickname'] = array('like','%'.$request['nickname'].'%');
        }
        $param['where']['message.status']   = array('lt',9);        //状态
        $param['order']              = 'message.create_time DESC';   //排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('Message')->message($param);
        return $result;
    }

    function messageAdd($request = array()){
        if(!$_GET['message_id']){
            $this->setLogicError('参数错误！'); return false;
        }else{
            $message = D('MessageList') ->where(array('id'=>$_GET['message_id']))->field('*')->find();
            $data['status'] = 1;
            $res = D('MessageList') ->where(array('id'=>$_GET['message_id']))->save($data);
            if($_GET['type'] == 2){
                $m_id = D('Master') ->select();
            }elseif($_GET['type'] == 1){
                $m_id = D('Member')->select();
            }
            foreach($m_id as $k => $v){
                $data['user_id'] = $v['id'];
                $data['user_type'] = $message['type'];
                $data['headline'] =$message['headline'];
                $data['content'] =$message['content'];
                $data['type'] = 4;
                $data['status'] = 0;
                $data['object_id'] = $_GET['message_id'];
                $data['create_time'] = time();
                $result = D('Message')->data($data)->add();
                if(!$result) {
                    $this->setLogicError('新增时出错！'); return false;
                }
            }
            $this->setLogicSuccess($result?'成功发布系统消息！':'成功发布系统消息！'); return true;
        }
    }
}