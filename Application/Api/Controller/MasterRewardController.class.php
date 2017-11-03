<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class MemberController
 * @package Api\Controller
 * 大师悬赏订单
 */
class MasterRewardController extends BaseController{
    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 正在悬赏订单
     * 大师token   186b4ce9761f4947acc9461c6e693fe1  06d7b6d0c1aac7bb5b3c795b1dc53554  611bca5bef0ed599518799c1cebe4a4c
     * 订单类型    type  1  指定自己  2  待回答
     * 分页参数    p
     * 悬赏订单        reward_id
     */
    public function rewardOrderList(){
        //大师ID不能为空
        if($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //订单类型  1  指定自己  2  待回答
        if($_POST['type'] != 1&&$_POST['type'] != 2){
            apiResponse('0','订单类型不能为空');
        }

        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        D('MasterReward','Logic') ->rewardOrderList(I('post.'));
    }

    /**
     * 已完成订单
     * 大师token    186b4ce9761f4947acc9461c6e693fe1  06d7b6d0c1aac7bb5b3c795b1dc53554  611bca5bef0ed599518799c1cebe4a4c
     * 订单类型     type  1  已回答  2  已采纳
     * 分页参数     p
     * 悬赏订单类型 reward_id
     */
    public function completeOrderList(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //订单类型  1  已回答  2  已采纳
        if($_POST['type'] != 1&&$_POST['type'] != 2){
            apiResponse('0','订单类型不能为空');
        }

        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        D('MasterReward','Logic') ->completeOrderList(I('post.'));
    }

    /**
     * 订单详情
     * 大师token   token  186b4ce9761f4947acc9461c6e693fe1  06d7b6d0c1aac7bb5b3c795b1dc53554  611bca5bef0ed599518799c1cebe4a4c
     * 悬赏订单ID  rorder_id
     * 订单类型    type  1  正在悬赏  2  已完成
     */
    public function rewardOrderInfo(){
        if($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(!$_POST['rorder_id']){
            apiResponse('0','悬赏订单ID不能为空');
        }
        if($_POST['type'] != 1&&$_POST['type'] != 2){
            apiResponse('0','选择类型');
        }
        D('MasterReward','Logic') ->rewardOrderInfo(I('post.'));
    }

    /**
     * 大师提交悬赏答案
     * 大师token   token  186b4ce9761f4947acc9461c6e693fe1  06d7b6d0c1aac7bb5b3c795b1dc53554  611bca5bef0ed599518799c1cebe4a4c
     * 悬赏订单ID  rorder_id
     * 悬赏答案    content
     * 图片上传    picture
     */
    public function rewardAnswer(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(!$_POST['rorder_id']){
            apiResponse('0','悬赏订单ID不能为空');
        }
        if(!$_POST['content']){
            apiResponse('0','请填写答案');
        }
        D('MasterReward','Logic') ->rewardAnswer(I('post.'));
    }
}
