<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class MemberController
 * @package Api\Controller
 * 用户悬赏订单
 */
class MemberRewardController extends BaseController{
    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 悬赏订单列表
     */
    public function rewardList(){
        if($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(!$_POST['p']){
            apiResponse('0','分页参数不能为空');
        }
        D('MemberReward','Logic') ->rewardList(I('post.'));
    }

    /**
     * 我的悬赏订单列表
     * 用户token       token     4e31b18e41db430722ef4559993322c9
     * 分页参数        p
     */
    public function myRewardList(){
        if(empty($_SERVER['HTTP_TOKEN'])&&empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif(!empty($_SERVER['HTTP_TOKEN'])){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if(!$_POST['p']){
            apiResponse('0','分页参数不能为空');
        }
        D('MemberReward','Logic') ->myRewardList(I('post.'));
    }

    /**
     * 我的悬赏订单详情
     * 用户token       token     4e31b18e41db430722ef4559993322c9
     * 悬赏ID          rorder_id
     */
    public function myRewardInfo(){

        if(empty($_SERVER['HTTP_TOKEN'])&&empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif(!empty($_SERVER['HTTP_TOKEN'])){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if(!$_POST['rorder_id']){
            apiResponse('0','悬赏订单ID不能为空');
        }
        D('MemberReward','Logic') ->myRewardInfo(I('post.'));
    }

    /**
     * 悬赏订单详情
     * 用户token       token     4e31b18e41db430722ef4559993322c9
     * 悬赏ID          rorder_id
     */
    public function rewardInfo(){
        if(empty($_SERVER['HTTP_TOKEN'])&&empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif(!empty($_SERVER['HTTP_TOKEN'])){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if(!$_POST['rorder_id']){
            apiResponse('0','悬赏订单ID不能为空');
        }
        D('MemberReward','Logic') ->rewardInfo(I('post.'));
    }

    /**
     * 采纳答案
     * 用户标识token     token
     * 悬赏订单ID        rorder_id
     * 答案ID            answer_id
     */
    public function adoptAnswer(){
        if(empty($_SERVER['HTTP_TOKEN'])&&empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif(!empty($_SERVER['HTTP_TOKEN'])){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if(empty($_POST['rorder_id'])){
            apiResponse('0','订单ID不能为空');
        }

        if(empty($_POST['answer_id'])){
            apiResponse('0','答案ID不能为空');
        }
        D('MemberReward','Logic') ->adoptAnswer(I('post.'));
    }

    /**
     * 我要围观
     * 用户标识token     token
     * 悬赏订单ID        rorder_id
     */
    public function gonnaWatch(){
        if(empty($_SERVER['HTTP_TOKEN'])&&empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif(!empty($_SERVER['HTTP_TOKEN'])){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if(empty($_POST['rorder_id'])){
            apiResponse('0','悬赏ID不能为空');
        }
        D('MemberReward','Logic') ->gonnaWatch(I('post.'));
    }

    /**
     * 提交悬赏
     * 用户标识   token     4e31b18e41db430722ef4559993322c9
     * 输入姓名   name
     * 性别       sex    1  男  2  女
     * 出生日期   birthday
     * 出生地     city_id
     * 标题       title
     * 问题详情   content
     * 悬赏类型   reward_id
     * 悬赏金额   reward_price
     * 围观类型   free_watch
     * 围观时间   reward_time
     * 匿名类型   is_anonymous
     * 图片详情   picture
     */
    public function addReward(){
        if(empty($_SERVER['HTTP_TOKEN'])&&empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif(!empty($_SERVER['HTTP_TOKEN'])){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['name'])){
            apiResponse('0','请输入您的姓名');
        }
        if($_POST['sex'] != 1&&$_POST['sex'] != 2){
            apiResponse('0','请选择您的性别');
        }
        if(empty($_POST['birthday'])){
            apiResponse('0','请选择您的出生日期');
        }
        if(empty($_POST['city_id'])){
            apiResponse('0','请选择您的出生地');
        }
        if(empty($_POST['title'])){
            apiResponse('0','请输入标题');
        }
        if(empty($_POST['content'])){
            apiResponse('0','请输入问题详情');
        }
        if(empty($_POST['reward_id'])){
            apiResponse('0','请选择悬赏类型');
        }
        if(empty($_POST['reward_price'])){
            apiResponse('0','请填写悬赏金额');
        }
        if($_POST['free_watch'] != 1&&$_POST['free_watch'] != 2){
            apiResponse('0','围观类型有误');
        }
        if(empty($_POST['reward_time'])){
            apiResponse('0','请选择围观时间');
        }
        if($_POST['is_anonymous'] != 1&&$_POST['is_anonymous'] != 2){
            apiResponse('0','匿名选择有误');
        }
        D('MemberReward','Logic') ->addReward(I('post.'));
    }

    /**
     * 悬赏类型
     */
    public function rewardType(){
        D('MemberReward','Logic') ->rewardType(I('post.'));
    }

    /**
     * 大师列表
     */
    public function masterList(){
        D('MemberReward','Logic') ->masterList(I('post.'));
    }

    /**
     * 服务支付页面
     */
    public function rewardPayPage(){
        if(empty($_SERVER['HTTP_TOKEN'])&&empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif(!empty($_SERVER['HTTP_TOKEN'])){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if(!$_POST['rorder_id']){
            apiResponse('0','悬赏ID不能为空');
        }
        if(!$_POST['price']){
            apiResponse('0','悬赏价格不能为空');
        }
        if(!$_POST['order_sn']){
            apiResponse('0','悬赏订单号不能为空');
        }
        D('MemberReward','Logic') ->rewardPayPage(I('post.'));
    }

    /**
     * 围观支付页面
     */
    public function watchPayPage(){
        if(empty($_SERVER['HTTP_TOKEN'])&&empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif(!empty($_SERVER['HTTP_TOKEN'])){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if(!$_POST['watch_id']){
            apiResponse('0','围观ID不能为空');
        }
        if(!$_POST['price']){
            apiResponse('0','围观价格不能为空');
        }
        if(!$_POST['order_sn']){
            apiResponse('0','订单号不能为空');
        }
    }

    /**
     * 悬赏订单详情
     * 用户标识   token
     * 悬赏订单id rorder_id
     */
    public function rewardInfoPage(){
        if(empty($_SERVER['HTTP_TOKEN'])&&empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif(!empty($_SERVER['HTTP_TOKEN'])){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if(!$_POST['rorder_id']){
            apiResponse('0','悬赏订单ID不能为空');
        }
        D('MemberReward','Logic') ->rewardInfoPage(I('post.'));
    }

    /**
     * 修改详情信息
     */
    public function modifyRewardInfo(){
        if(empty($_SERVER['HTTP_TOKEN'])&&empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif(!empty($_SERVER['HTTP_TOKEN'])){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if(!$_POST['rorder_id']){
            apiResponse('0','悬赏订单ID不能为空');
        }
        D('MemberReward','Logic') ->modifyRewardInfo(I('post.'));
    }

    /**
     * 删除悬赏信息
     * 用户标识   token
     * 悬赏订单id rorder_id
     */
    public function deleteReward(){
        if(empty($_SERVER['HTTP_TOKEN'])&&empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif(!empty($_SERVER['HTTP_TOKEN'])){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        if(!$_POST['rorder_id']){
            apiResponse('0','悬赏订单ID不能为空');
        }
        D('MemberReward','Logic') ->deleteReward(I('post.'));
    }
}
