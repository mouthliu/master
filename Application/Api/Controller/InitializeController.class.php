<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class InitializeController
 * @package Api\Controller
 * APP启动基本配置
 */
class InitializeController extends BaseController{

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 商家端检查更新
     * 传递参数的方式：post
     * 需要传递的参数：null
     */
    public function masterStart(){
        D('Initialize','Logic')->masterStart();
    }

    /**
     * 商家端检查更新
     * 传递参数的方式：post
     * 需要传递的参数：null
     */
    public function memberStart(){
        D('Initialize','Logic')->memberStart();
    }
    /**
     * 下载商家端APP
     */
    public function downloadNewVersionMaster(){
        $file = "./Uploads/Version/dashiduan.apk";
        header("Content-type: application/vnd.android.package-archive;");
        header('Content-Disposition: attachment; filename="' . 'dashiduan.apk' . '"');
        header("Content-Length: ". filesize($file));
        readfile($file);
    }

    /**
     * 下载用户端APP
     */
    public function downloadNewVersionMember(){
        $file = "./Uploads/Version/dashi.apk";
        header("Content-type: application/vnd.android.package-archive;");
        header('Content-Disposition: attachment; filename="' . 'dashi.apk' . '"');
        header("Content-Length: ". filesize($file));
        readfile($file);
    }
}