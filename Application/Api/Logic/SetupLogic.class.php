<?php
namespace Api\Logic;
/**
 * 设置界面通用版
 */
class SetupLogic extends BaseLogic{
    /**
     * 大师—设置
     */
    public function masterSetup($request = array()){
        $master = $this ->searchMaster($request['token']);
        $service = M('config') ->where(array('id'=> 55)) ->getField('value');
        $result['min_red'] = $master['min_red']?$master['min_red'].'':'0.00';
        $result['max_red'] = $master['max_red']?$master['max_red'].'':'0.00';
        $result['service_line'] = $service;
        apiResponse('1','',$result);
    }

    /**
     * 大师—红包范围
     */
    public function redRange($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where['id'] = $master['id'];
        $data['min_red'] = $request['min_red'];
        $data['max_red'] = $request['max_red'];
        $data['update_time'] = time();
        $res = $this ->easyMysql('Master','2',$where,$data);
        if(!$res){
            apiResponse('0','设置失败');
        }
        apiResponse('1','设置成功');
    }

    /**
     * 通用版—意见反馈
     */
    public function feedBack($request = array()){
        if($request['user_type'] == 1){
            $res = $this ->searchMember($request['token']);
        }else{
            $res = $this ->searchMaster($request['token']);
        }
        $data['order_sn'] = $request['order_sn']?$request['order_sn']:'';
        $data['user_type'] = $request['user_type'];
        $data['user_id']  = $res['id'];
        $data['type']     = $request['type'];
        $data['content']  = $request['content'];
        $data['telephone'] = $request['telephone'];
        $data['create_time'] = time();
        $result = $this ->easyMysql('Feedback','1','',$data);
        if(!$result){
            apiResponse('0','提交失败');
        }
        apiResponse('1','提交成功');
    }

    /**
     * 关于我们
     * 传递参数的方式：post
     * 需要传递的参数：无
     */
    public function aboutUs($request = array()){
        if($request['type'] != 1&&$request['type'] != 2){
            apiResponse('0','类型传递有误');
        }

        $config = D('Config')->parseList();
        $result_data = array();
        $result_data['company_name'] = $config['COMPANY_NAME'];
        $result_data['copyright'] = $config['COPYRIGHT'];
        if($request['type'] == 1){
            $result_data['ios_status'] = $config['IOS_STATUS_MEMBER'];
        }else{
            $result_data['ios_status'] = $config['IOS_STATUS_MASTER'];
        }

        apiResponse('1','',$result_data);
    }

    /**
     * 分享页面
     * 传递参数的方式：post
     * 需要传递的参数：无
     */
    public function sharePage($request = array()){
        if($request['user_type'] == 1){
            $result = $this ->searchMember($request['token']);
            $data['m_id'] = $result['id'];
            $where['user_id'] = $result['id'];
        }else{
            $result = $this ->searchMaster($request['token']);
            $data['master_id'] = $result['id'];
            $where['user_id']  = $result['id'];
        }

        $where['user_type'] = $request['user_type'];
        $where['start_time'] = strtotime(date('Y-m-d'));
        $record = $this ->easyMysql('RedRecord',3,$where);
        if($record){
            apiResponse('0','你今天已经领取过红包了');
        }

        $res_list = $this ->easyMysql('RedList','3',array('id'=>1));
        if(!$res_list){
            apiResponse('0','红包信息有误');
        }

        //加入数据
        $data['order_sn']    = date('Ymd').rand(1000000,9999999);
        $data['title']       = $res_list['title'];
        $data['type']        = 3;
        $data['pay_type']    = 4;
        $data['pay_status']  = 1;
        $data['price']       = $res_list['price'];
        $data['create_time'] = time();
        $data['status']      = 1;
        $red_pachage = $this ->easyMysql('RedPackage','1','',$data);
        if(!$red_pachage){
            apiResponse('0','领取红包失败');
        }

        unset($data);
        $data['user_type'] = $request['user_type'];
        $data['user_id']   = $result['id'];
        $data['start_time'] = strtotime(date('Y-m-d'));
        $data['end_time']  = time()+86400;
        $data['create_time'] = time();
        $red_record = $this ->easyMysql('RedRecord','1','',$data);
        if(!$red_record){
            apiResponse('0','记录红包领取有误');
        }

        if($request['user_type'] == 1){
            $res = $this ->setType('Member',array('id'=>$result['id']),'balance',$res_list['price'],1);
        }else{
            $res = $this ->setType('Master',array('id'=>$result['id']),'balance',$res_list['price'],1);
        }
        if(!$res){
            apiResponse('0','添加金额失败');
        }

        $detail = $this ->addDetail($request['user_type'], $result['id'], 1, $res_list['title'], 1, $res_list['price']);
        if(!$detail){
            apiResponse('0','写入详情失败');
        }

        apiResponse('1','分享成功');
    }
}