<?php
namespace Manager\Logic;

/**
 * Class GoodsLogic
 * @package Manager\Logic
 * 商品模块逻辑层
 */
class GoodsLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取列表
     */
    function getList($request = array()) {
        if(!empty($request['nickname'])) {
            $param['where']['nickname']   = array('like','%'.$request['nickname'].'%');
        }
        if(!empty($request['frame'])) {
            $param['where']['goods.frame'] = $request['frame'];
        }
        if(!empty($request['goods_name'])) {
            $param['where']['goods.goods_name']  = array('like','%'.$request['goods_name'].'%');
        }
        if(!empty($request['first_type'])) {
            $param['where']['goods.first_type'] = $request['first_type'];
        }
        if(!empty($request['is_show'])) {
            $param['where']['goods.is_show'] = $request['is_show'];
        }
        if(!empty($request['goods_type'])) {
            $param['where']['goods.goods_type'] = $request['goods_type'];
        }
        if(!empty($request['start_time']) & !empty($request['end_time'])){
            $start_time = strtotime($request['start_time']);
            $end_time   = strtotime($request['end_time']);
            $param['where']['goods.create_time']   = array('between',"$start_time,$end_time");
        }elseif(!empty($request['start_time']) && empty($request['end_time'])){
            $start_time = strtotime($request['start_time']);
            $param['where']['goods.create_time']   = array('egt',"$start_time");
        }elseif(empty($request['start_time']) && !empty($request['end_time'])){
            $end_time   = strtotime($request['end_time']);
            $param['where']['goods.create_time']   = array('elt',"$end_time");
        }else{

        }
        $param['order']             = 'goods.create_time DESC';   //排序
        $param['where']['goods.status']   = array('lt',9);        //状态
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('Goods')->getList($param);
        echo "<meta charset='UTF-8'>";
        foreach($result as $key=>$val){
            foreach($val as $kk=>$vv){
                foreach(explode(",",$vv['first_type']) as $kkk=>$vvv){
                    $where['id'] = $vvv;
                    $one  = D('Goods_type')->where($where)->field("type_name")->find();
                    $result[$key][$kk]['first_name'] .= $one['type_name']." ";
                }
            }
        }
        foreach($result as $key=>$val){
            foreach($val as $kk=>$vv){
                foreach(explode(",",$vv['goods_type']) as $kkk=>$vvv){
                    $where['id'] = $vvv;
                    $one  = D('Goods_type')->where($where)->field("type_name")->find();
                    $result[$key][$kk]['type_name'] .= $one['type_name']." ";
                }
            }
        }
        return $result;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['goods.id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['goods.status'] = array('lt',9);
        $row = D('Goods')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['goods_pic'] = api('System/getFiles',array($row['goods_pic']));
        foreach(explode(',',$row['first_type']) as $key => $val){
            $where['id'] = $val;
            $one  = D('Goods_type')->where($where)->field("type_name")->find();
            $row['first_name'] .= $one['type_name']." ";
        }
        foreach(explode(',',$row['goods_type']) as $key => $val){
            $where['id'] = $val;
            $one  = D('Goods_type')->where($where)->field("type_name")->find();
            $row['type_name'] .= $one['type_name']." ";
        }
        return $row;
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
            $this->setLogicError('数据导入失败!'); return false;
        }
    }
}