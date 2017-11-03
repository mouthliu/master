<?php
namespace Manager\Logic;

/**
 * Class MasterLogic
 * @package Manager\Logic
 * 大师管理逻辑层
 */
class MasterLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取列表
     */
    function getList($request = array()) {
        if(!empty($request['account'])) {
            $param['where']['account']   = array('like','%'.$request['account'].'%');
        }
        if(!empty($request['nickname'])) {
            $param['where']['nickname']  = array('like','%'.$request['nickname'].'%');
        }
        if(!empty($request['sex'])){
            $param['where']['sex']  = $request['sex'];
        }elseif($request['sex'] === 0){
            $param['where']['sex']  = 0;
        }
        if(!empty($request['auth_status'])){
            $param['where']['auth_status']  = $request['auth_status'];
        }elseif($request['auth_status'] === 0){
            $param['where']['auth_status']  = 0;
        }

        if(!empty($request['start_time']) & !empty($request['end_time'])){
            $start_time = strtotime($request['start_time']);
            $end_time   = strtotime($request['end_time']);
            $param['where']['create_time']   = array('between',"$start_time,$end_time");
        }elseif(!empty($request['start_time']) && empty($request['end_time'])){
            $start_time = strtotime($request['start_time']);
            $param['where']['create_time']   = array('egt',"$start_time");
        }elseif(empty($request['start_time']) && !empty($request['end_time'])){
            $end_time   = strtotime($request['end_time']);
            $param['where']['create_time']   = array('elt',"$end_time");
        }else{

        }
        $param['order']             = 'create_time DESC';   //排序
        $param['where']['status']   = array('lt',9);        //状态
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('Master')->getCustomList($param);

        return $result;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['m.id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['m.status'] = array('lt',9);
        $row = D('Master')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['head_pic'] = api('System/getFiles',array($row['head_pic']));
        return $row;
    }

    /**
     * @param array $request
     * @return bool|mixed
     * 修改密码
     */
    function rePass($request = array()) {
        if(empty($request['new_password'])) {
            $this->setLogicError('请输入新密码！'); return false;
        } if(strlen($request['new_password']) < 6 || strlen($request['new_password']) > 18) {
            $this->setLogicError('新密码长度在6--18位之间！'); return false;
        } if($request['re_new_password'] != $request['new_password']) {
            $this->setLogicError('确认新密码与新密码不一致！'); return false;
        }

        //验证原密码是否正确
        //后台修改前台用户密码，应该不需要原来的密码，而且新增和修改密码是同一页，此验证会有冲突
//         $password = D('Member')->where(array('id'=>$request['id']))->getField('password');

//         if(!($password == MD5(MD5($request['old_password'])))) {
//             $this->setLogicError('原密码不正确！'); return false;
//         }

        //修改
        $data['password'] = MD5($request['new_password']);
        $where['id'] = $request['id'];
        $result = D('Master')->where($where)->data($data)->save();
        if($result){
            $this->setLogicSuccess('修改密码成功！'); return true;
        }else{
            $this->setLogicError('修改密码失败！'); return false;
        }
    }

    /**
     * @param array $data
     * @return array
     * 处理提交数据 进行加工或者添加其他默认数据
     */
    protected function processData($data = array()) {
        if(empty($data['id'])) {
            $data['password'] = MD5($data['password']);
            $data['status'] = 1;

            $data['easemob_account'] = time().rand(00001,99999);
            $data['easemob_password'] = 'TXunDaMjMf';
            $data['create_time']     = time();
            //注册环信
            $option['username'] =  $data['easemob_account'];
            $option['password'] = 'TXunDaMjMf';
            $add_easemob_json = D('Easemob','Service')->openRegister($option);
            $add_easemob_arr  = json_decode($add_easemob_json,true);
        }
        return $data;
    }


    /**
     * @param array $request
     * @return bool
     * 导出数据excel表格
     */
    function export($request = array()) {
        //字段数据
        $fields_data = $request['fields_data'];
        //获取会员列表
        $list = D('Member')->select();
        //转换一些数据格式  例如：时间-字符串 性别-字符串等
        foreach($list as $key => $value) {
            //时间转换
            $list[$key]['create_time']  = date('Y-m-d H:i',$value['create_time']);
            //性别转换
            $list[$key]['sex']          = $value['sex'] == 1 ? '男' : '女';
        }
        //执行导出函数
        $result = api('Excel/exportToExcel',array($fields_data, $list, 'MEMBER'));
        //判断成功失败
        if($result === false) {
            $this->setLogicError('导出文件出错！'); return false;
        }
        return true;
    }

    /**
     * @param array $request
     * @return bool
     * 导入数据
     */
    function import($request = array()) {
        //判断是否上传了导入文件
        if(empty($request['import_file'])) {
            $this->setLogicError('您未上传导入文件！'); return false;
        }
        //获取导入文件中数据
        $data = api('Excel/readExcelToData',array($request['import_file']));
        //文件错误
        if($data === false) {
            $this->setLogicError('导入文件格式有误！'); return false;
        }
        //数据为空
        if(empty($data)) {
            $this->setLogicError('导入数据为空！'); return false;
        }
        //生成一些其他数据
        foreach($data as $key => $value){
            $data[$key]['create_time']  = time();                   //创建时间
            $data[$key]['password']     = MD5($value['password']);  //密码加密
        }
        //存入数据库  //TODO 是否要验证重复
        $result = D('Member')->addAll($data);
        if($result) {
            //删除文件记录
            return true;
        } else {
            $this->setLogicError('数据导入失败！'); return false;
        }
    }

    public function findVerify($request = array()){
        if(!empty($request['id'])) {
            $param['where']['m.id'] = $request['id'];
        } else {
            $this->setLogicError('用户ID为空！'); return false;
        }
        $param['where']['m.status'] = array('lt',9);
        $row = D('Master') ->findVerify($param);
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
            $password = strlen($request['password']);
            if($password < 6){
                $this->setLogicError('密码不能小于6位！'); return false;
            }
            $data['password'] = md5($request['password']);
            $data['create_time'] = time();
            $data['token'] = md5(time().rand(10000,99999));
            $data['expire_time'] = strtotime(date('Y-m-d',time())) + 15*86400;
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
}