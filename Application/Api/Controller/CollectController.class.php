<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class MemberController
 * @package Api\Controller
 * 收藏模块
 */
class CollectController extends BaseController{

    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 我的收藏列表
     * 用户token    token
     * 分页参数     p
     */
    public function myCollectList(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        D('Collect','Logic') ->myCollectList(I('post.'));
    }

    /**
     * 收藏商品
     * 用户token      token
     * 商品ID         goods_id
     */
    public function collectGoods(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['goods_id'])){
            apiResponse('0','商品ID不能为空');
        }
        D('Collect','Logic') ->collectGoods(I('post.'));
    }

    /**
     * 收藏商品
     * 用户token      token
     * 商品ID         goods_id
     */
    public function concellCollect(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['goods_id'])){
            apiResponse('0','商品ID不能为空');
        }
        D('Collect','Logic') ->concellCollect(I('post.'));
    }

    /**
     * 删除收藏
     * 用户token    token   adf962e22137a8860ddf1f81bcc9c094
     * 收藏ID       collect_id
     */
    public function deleteCollect(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['collect_id'])){
            apiResponse('0','收藏ID不能为空');
        }
        D('Collect','Logic') ->deleteCollect(I('post.'));
    }

    /**
     * 我的关注列表
     * 用户token    token   adf962e22137a8860ddf1f81bcc9c094
     * 分页参数     p
     */
    public function myFollowList(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        D('Collect','Logic') ->myFollowList(I('post.'));
    }

    /**
     * 关注大师
     * 用户token    token   adf962e22137a8860ddf1f81bcc9c094
     * 大师ID       master_id
     */
    public function followMaster(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['master_id'])){
            apiResponse('0','大师ID不能为空');
        }
        D('Collect','Logic') ->followMaster(I('post.'));
    }

    /**
     * 取消关注大师
     * 用户token    token   adf962e22137a8860ddf1f81bcc9c094
     * 大师ID       master_id
     */
    public function concellFollow(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['master_id'])){
            apiResponse('0','大师ID不能为空');
        }
        D('Collect','Logic') ->concellFollow(I('post.'));
    }

    /**
     * 取消关注
     * 用户token    token   adf962e22137a8860ddf1f81bcc9c094
     * 大师ID       master_id
     */
    public function deleteFollow(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['follow_id'])){
            apiResponse('0','关注ID不能为空');
        }
        D('Collect','Logic') ->deleteFollow(I('post.'));
    }
}
