<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class MemberController
 * @package Api\Controller
 * 商品管理模块
 */
class SocialController extends BaseController{
    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }

    /*
    * 大师端—协会列表
    * 用户ID   账号token
    */
    public function socialList(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        D('Social','Logic') ->socialList(I('post.'));
    }

    /*
    * 申请加入协会
    * 用户ID   账号token
    */
    public function application(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['social_id'])){
            apiResponse('0','协会ID不能为空');
        }
        D('Social','Logic') ->application(I('post.'));
    }

    /*
    * 申请记录
    * 用户ID   账号token
    */
    public function record(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        D('Social','Logic') ->record(I('post.'));
    }

    /*
    * 创建协会
     * 用户ID   账号token
     * 协会名称         social_name
     * 协会地点         address
     * 创建时间         start_time
     * 协会联系人1      one_contact
     * 协会联系人2      two_contact
     * 协会资料         social_info
     * 协会头像         social_head_pic
     * 协会相册         social_pic
    */
    public function createSocial(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['social_name'])){
            apiResponse('0','请输入协会名称');
        }
        if(empty($_POST['address'])){
            apiResponse('0','请输入协会地点');
        }
        if(empty($_POST['start_time'])){
            apiResponse('0','请输入协会创建时间');
        }
        if(empty($_POST['one_contact'])){
            apiResponse('0','请输入协会联系人1');
        }
        if(empty($_POST['two_contact'])){
            apiResponse('0','请输入协会联系人2');
        }
        if(empty($_POST['social_info'])){
            apiResponse('0','请输入协会资料');
        }
        if($_POST['one_contact'] == $_POST['two_contact']){
            apiResponse('0','请填写两个联系人');
        }
        D('Social','Logic') ->createSocial(I('post.'));
    }

    /*
    * 协会相册
    * 用户ID   账号token
    */
    public function socialAlbum(){
        if(empty($_POST['social_id'])){
            apiResponse('0','协会ID不能为空');
        }
        D('Social','Logic') ->socialAlbum(I('post.'));
    }

    /*
    * 上传相册
    * 用户ID   账号token
    */
    public function addAlbum(){
        if(empty($_POST['social_id'])){
            apiResponse('0','协会ID不能为空');
        }
        D('Social','Logic') ->addAlbum(I('post.'));
    }

    /*
    * 协会成员
    * 用户ID   账号token
    */
    public function socialPeople(){
        if(empty($_POST['social_id'])){
            apiResponse('0','协会ID不能为空');
        }
        D('Social','Logic') ->socialPeople(I('post.'));
    }

    /*
    * 协会详情
    * 用户ID   账号token
    */
    public function socialInfo(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['social_id'])){
            apiResponse('0','协会ID不能为空');
        }
        D('Social','Logic') ->socialInfo(I('post.'));
    }

    /*
    * 退出协会
    * 用户ID   账号token
    */
    public function quitSocial(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if(empty($_POST['social_id'])){
            apiResponse('0','协会ID不能为空');
        }
        D('Social','Logic') ->quitSocial(I('post.'));
    }

    /*
     * 协会管理
     */
    public function socialManage(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        D('Social','Logic') ->socialManage(I('post.'));
    }
}
