<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class ArticleController
 * @package Api\Controller
 * 文章系统
 */
class ArticleController extends BaseController{
    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 围观说明
     */
    public function onlookers(){
        D('Article','Logic')->onlookers();
    }

    /**
     * 填写说明
     */
    public function writing(){
        D('Article','Logic')->writing();
    }

    /**
     * 帮助中心
     */
    public function requiredRead(){
        D('Article','Logic')->requiredRead();
    }

    /**
     * 用户使用协议
     */
    public function memberAgreement(){
        D('Article','Logic')->memberAgreement();
    }
}
