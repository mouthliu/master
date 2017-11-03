<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class FeedbackController
 * @package Api\Controller
 * 意见反馈
 */
class FeedbackController extends BaseController{
    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }
    /*
     * 意见反馈
     * 用户ID   m_id
     * 意见反馈内容
     */
    public function feedback(){
        D('Feedback','Logic')->feedback(I('post.'));
    }

//    /**
//     * 环信测试
//     */
//    public function easmobTest(){
//        $option['username'] = '15340562994';
//        $option['password'] = time().'';
//        $res = D('Easemob','Service')->createUser($option);
//        dump($res);
//    }
}
