<?php
namespace Manager\Controller;

/**
 * Class MessageController
 * @package Manager\Controller
 * 新闻控制器
 */
class NewsController extends BaseController {

    public function getAddRelation()
    {
        $news_type = M('NewsType') ->where(array('status'=>array('neq',9))) ->field('id as news_type_id, type_name') ->select();
        if(!$news_type){
            $news_type = array();
        }
        $this ->assign('news_type',$news_type);
    }

    public function getUpdateRelation()
    {
        $news_type = M('NewsType') ->where(array('status'=>array('neq',9))) ->field('id as news_type_id, type_name') ->select();
        if(!$news_type){
            $news_type = array();
        }
        $this ->assign('news_type',$news_type);
    }
    public function getIndexRelation()
    {
        $news_type = M('NewsType') ->where(array('status'=>array('neq',9))) ->field('id as news_type_id, type_name') ->select();
        if(!$news_type){
            $news_type = array();
        }
        $this ->assign('news_type',$news_type);
    }
}
