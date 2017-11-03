<?php
namespace Manager\Logic;

/**
 * Class MemberLogic
 * @package Manager\Logic
 * 商品类别逻辑层
 */
class GoodsTypeLogic extends BaseLogic {
    /**
     * @param array $request
     * @return array
     * 获取列表
     */
    function getList($request = array()){
        if($request['type_name']){
            $param['where']['type_name'] = array('like','%'.$request['type_name'].'%');
        }
        $param['where']['parent_id'] = 0;                    //状态
        $param['order']              = 'create_time DESC';   //排序
        $param['where']['status']    = array('lt',9);        //状态
        $param['page_size']          = C('LIST_ROWS');        //页码
        $param['parameter']          = $request;             //拼接参数
        $result = D('GoodsType') ->getList($param);
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
        $param['where']['status'] = array('lt',9);
        $row = D('GoodsType')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['photo'] = api('System/getFiles',array($row['photo']));
        return $row;
    }

    /**
     * @param array $request
     * @return array
     * 获取列表
     */
    function sonGetList($request = array()){
        if($request['parent_id']){
            $param['where']['parent_id'] = $request['parent_id'];
            $_SESSION['parent_id'] = $request['parent_id'];
        }else{
            $param['where']['parent_id'] = $_SESSION['parent_id'];
        }
        if($request['type_name']){
            $param['where']['type_name'] = array('like','%'.$request['type_name'].'%');
        }
        $param['order']              = 'create_time DESC';   //排序
        $param['where']['status']    = array('lt',9);        //状态
        $param['page_size']          = C('LIST_ROWS');        //页码
        $param['parameter']          = $request;             //拼接参数
        $result = D('GoodsType') ->getList($param);
        return $result;
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
            $data['create_time'] = time();
            $result = D($model)->data($data)->add();
            if(!$result) {
                $this->setLogicError('新增时出错！'); return false;
            }
            //行为日志
            api('Manager/ActionLog/actionLog', array('add',$model,$result,AID));
        } else {
            //创建修改参数
            $where['id'] = $request['id'];
            $data['update_time'] = time();
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

    /**
     * @param array $request
     * @return bool|mixed
     * 删除前判断
     */
    function beforeRemove($request = array()){
        if(!empty($request)){
            $model = $request['model'];
            $where['parent_id'] = $request['ids'];
            $result = D($model)->where($where)->select();
            if($result){
                $this->setLogicError('该类已存在子分类！'); return false;
            }
        }
    }
}