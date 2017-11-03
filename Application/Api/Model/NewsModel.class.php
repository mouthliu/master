<?php
namespace Api\Model;
use Think\Model;

/**
 * Class BaseModel
 * @package Manager\Model
 * 新闻类
 */
class NewsModel extends BaseModel {
    function getList($param = array()){

    }
    function findRow($param = array()){

    }
    public function selectNews($where = array(), $field = '*', $order = '', $limit = '', $page = ''){
        if(!empty($page)){
            $result = $this ->alias('news') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = news.master_id',
                    'LEFT JOIN '.C('DB_PREFIX').'news_type news_type ON news_type.id = news.news_type',
                ))
                ->field($field) ->order($order) ->page($page,10) ->select();
        }elseif($limit == 1){
            $result = $this ->alias('news') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = news.master_id',
                    'LEFT JOIN '.C('DB_PREFIX').'news_type news_type ON news_type.id = news.news_type',
                ))
                ->field($field) ->find();
        }else{
            $result = $this ->alias('news') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = news.master_id',
                    'LEFT JOIN '.C('DB_PREFIX').'news_type news_type ON news_type.id = news.news_type',
                ))
                ->field($field) ->order($order) ->limit($limit) ->select();
        }

        return $result;
    }
}