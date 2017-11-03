<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class NewsController
 * @package Api\Controller
 * 新闻模块
 */
class NewsController extends BaseController{
    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }
    /**
     * 大师—新闻类别表
     */
    public function newsType(){
        D('News','Logic') -> newsType(I('post.'));
    }
    /**
     * 大师—添加新闻
     * 大师的标识    token
     * 新闻标题      title
     * 新闻内容      content
     * 新闻类别      news_type
     * 新闻图片      picture
     */
    public function addNews(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['title'])){
            apiResponse('0','请填写文章标题');
        }
        if(empty($_POST['content'])){
            apiResponse('0','请填写文章内容');
        }
        if(empty($_POST['news_type'])){
            apiResponse('0','请选择文章类别');
        }
        D('News','Logic') -> addNews(I('post.'));
    }

    /**
     * 用户端—新闻列表
     */
    public function newsList(){
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        D('News','Logic') -> newsList(I('post.'));
    }

    /**
     * 用户端—新闻详情
     */
    public function newsInfo(){
        if(empty($_POST['news_id'])){
            apiResponse('0','新闻ID不能为空');
        }
        D('News','Logic') -> newsInfo(I('post.'));
    }

    /**
     * 大师端—新闻列表
     * 大师token    token
     * 分页参数     p
     */
    public function masterNewsList(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        D('News','Logic') -> masterNewsList(I('post.'));
    }

    /**
     * 大师端—删除新闻
     * 大师token    token
     * 新闻id       news_id
     */
    public function deleteNews(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['news_id'])){
            apiResponse('0','新闻id不能为空');
        }
        D('News','Logic') -> deleteNews(I('post.'));
    }

    /**
     * 大师端—新闻详情
     * 大师token    token
     * 新闻id       news_id
     */
    public function masterNewsInfo(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['news_id'])){
            apiResponse('0','新闻id不能为空');
        }
        D('News','Logic') -> masterNewsInfo(I('post.'));
    }

    /**
     * 大师端—修改新闻详情
     */
    public function modifyNewsInfo(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['news_id'])){
            apiResponse('0','新闻id不能为空');
        }
        D('News','Logic') -> modifyNewsInfo(I('post.'));
    }
}
