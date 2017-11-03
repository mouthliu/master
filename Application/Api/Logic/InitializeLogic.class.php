<?php
namespace Api\Logic;

/**
 * Class InitializeLogic
 * @package Api\Logic
 * APP启动基本配置
 */
class InitializeLogic extends BaseLogic{

    /**
     * APP启动页
     */
    public function masterStart(){

        $config = $this->getConfig();
        $result_data = array();
        $result_data['android_version'] = ''.$config['ANDROID_VERSION_MASTER'];
        $result_data['android_link'] = ''.$config['ANDROID_LINK_MASTER'];
        $result_data['ios_status'] = ''.$config['IOS_STATUS_MASTER'];

        apiResponse('1','请求成功',$result_data);
    }

    /**
     * 商家端检查更新
     * 传递参数的方式：post
     * 需要传递的参数：null
     */
    public function memberStart(){
        $config = $this->getConfig();
        $result_data = array();
        $result_data['android_version'] = ''.$config['ANDROID_VERSION_MEMBER'];
        $result_data['android_link'] = ''.$config['ANDROID_LINK_MEMBER'];
        $result_data['ios_status'] = ''.$config['IOS_STATUS_MEMBER'];

        apiResponse('1','请求成功',$result_data);
    }
}