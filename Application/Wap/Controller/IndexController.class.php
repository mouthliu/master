<?php
namespace Wap\Controller;

class IndexController extends BaseController{

    public function _initialize(){
        parent::_initialize();
    }
    function index() {
        $this->display('index');exit;
    }

    function augur(){
        //获取大师相关信息
        if(!$_REQUEST['master_id']){
            $this ->error('大师id不能为空');
        }
        $where = array('id'=>$_REQUEST['master_id'],'status'=>1);
        $field = 'id as master_id, nickname, head_pic, field_id, auth_status, social_id, score, introduction';
        $master = $this ->easyMysql('Master','3',$where,'',$field);
        if(!$master){
            $this ->error('大师信息有误');
        }
        $head_pic = $this ->searchPhoto($master['head_pic']);
        $master['head_pic'] = $head_pic?$head_pic:C("API_URL").'/Uploads/Member/default.png';

        if(!empty($master['field_id'])){
            $field_list = explode(',',$master['field_id']);
            $field_info = array();
            foreach($field_list as $key =>$val){
                $field_name = $this ->easyMysql('Field',3,array('id'=>$val,'status'=>array('neq',9)),'','id as field_id, field_name');
                if(!empty($field_name)){
                    $field_info[] = $field_name;
                }
            }
        }
        $this ->assign('field', $field_info);
        if($master['social_id'] != 0){
            $master['social_status'] = '1';
            $social_name = $this ->easyMysql('Social',5,array('id'=>$master['social_id'],'status'=>array('neq',9)),'','social_name');
            $master['social_name'] = $social_name?$social_name:'';
        }else{
            $master['social_status'] = '2';
            $master['social_name'] = '';
        }

        //获取大师的服务
        $where = array('m_s.master_id'=>$_REQUEST['master_id'],'m_s.status'=>array('neq',9),'service.status'=>array('neq',9));
        $field = 'm_s.id as m_s_id, m_s.price, service.title, service.content, service.picture';
        $order = 'm_s.create_time desc';
        $service = $this ->showService($where, $field, $order);
        if(!$service){
            $service = array();
        }else{
            foreach($service as $k => $v){
                $service[$k]['picture'] = $this ->searchPhoto($v['picture']);
            }
        }
        $this ->assign('service', $service);
        //获取大师的评论
        $where = array('comment.master_id'=>$_REQUEST['master_id'], 'comment.status'=>array('neq',9));
        $field = 'comment.id as comment_id, comment.content, comment.rank, comment.create_time, member.nickname, member.head_pic, comment.picture';
        $order = 'comment.create_time desc';
        $comment = $this ->commentList($where, $field, $order, '', 2);
        if(!$comment){
            $comment = array();
            $comment_num = '0';
        }else{
            $comment_num = count($comment);
            foreach($comment as $k => $v){
                unset($head_pic);
                unset($picture);
                unset($pic);
                $head_pic = $this ->searchPhoto($v['head_pic']);
                $comment[$k]['head_pic'] = $head_pic?$head_pic:'/Uploads/Member/default.png';
                $comment[$k]['create_time'] = date('Y-m-d',$v['create_time']);
                if($v['picture'] != ''){
                    $pic = array();
                    $picture = explode(',',$v['picture']);
                    foreach($picture as $key => $val){
                        unset($photo);
                        $photo = $this ->searchPhoto($val);
                        $pic[$key]['picture'] = $photo?$photo:'';
                    }
                }
                $comment[$k]['picture'] = $pic?$pic:array();
            }
        }
//        $master['comment'] = $comment;
        $this ->assign('comment',$comment);

        $master['comment_num'] = $comment_num?$comment_num.'':'0';
        //获取大师宝阁
        $where = array('master_id'=>$_REQUEST['master_id'],'status'=>1);
        $field = 'id as goods_id, goods_pic, goods_name, price';
        $order = 'create_time asc';
        $goods = $this ->easyMysql('Goods','4',$where,'',$field,$order,'',3);
        if(!$goods){
            $goods = array();
        }else{
            foreach($goods as $k => $v){
                unset($picture);
                unset($goods_num);
                $picture = $this ->searchPhoto($v['goods_pic']);
                $goods[$k]['goods_pic'] = $picture?$picture:'';
                $goods[$k]['nickname'] = $master['nickname'];
                $goods[$k]['head_pic'] = $master['head_pic']?$master['head_pic']:C('API_URL').'/Uploads/Member/default.png';;
                $goods[$k]['auth_status'] = $master['auth_status'];
                $goods[$k]['social_status'] = $master['social_status'];
                $goods_num = $this ->getOrderNum($v['goods_id']);
                $goods[$k]['order_num'] = $goods_num?$goods_num.'':'0';
            }
        }
        $this ->assign('goods',$goods);
//        $master['goods'] = $goods;
        //获取大师的文章
        $where = array('news.master_id'=>$_REQUEST['master_id'],'news.status'=>1);
        $field = 'news.id as news_id, news.title, news_type.type_name, master.nickname, news.create_time, news.browse_times, news.picture';
        $order = 'news.create_time desc';
        $news  = $this ->selectNews($where, $field, $order, 3);
        if(!$news){
            $news = array();
        }else{
            foreach($news as $k => $v){
                unset($picture);
                $picture = $this ->searchPhoto($v['picture']);
                $news[$k]['news_pic'] = $picture?$picture:'';
                $news[$k]['create_time'] = date('Y.m.d',$v['create_time']);
            }
        }
//        $master['news'] = $news;
        $this ->assign('news',$news);
        //查询大师的回答
        $where = array('answer.master_id'=>$_REQUEST['master_id'],'answer.status'=>array('neq',9));
        $field = 'answer.id as answer_id, rorder.title, rorder.reward_price, rorder.watch_man, rorder.create_time, rorder.is_anonymous, member.nickname, member.head_pic, reward.reward_name';
        $order = 'answer.create_time desc';
        $reward = $this ->selectAnswerList($where, $field, $order, '', 3);
        if(!$reward){
            $reward = array();
        }else{
            foreach($reward as $k => $v){
                unset($head_pic);
                if($v['is_anonymous'] == 2){
                    $reward[$k]['head_pic'] = C("API_URL").'/Uploads/Member/default.png';
                    $reward[$k]['nickname'] = '匿名用户';
                }else{
                    $head_pic = $this ->searchPhoto($v['head_pic']);
                    $reward[$k]['head_pic'] = $head_pic?$head_pic:C('API_URL').'/Uploads/Member/default.png';
                }
                $reward[$k]['create_time'] = date('Y-m-d',$v['create_time']);
            }
        }
//        $master['reward'] = $reward;
        $this ->assign('reward',$reward);
        $order_num = $this ->serviceOrderNum($master['master_id']);
        $master['order_num'] = $order_num?$order_num.'':'0';
        $this ->assign('master',$master);
        $this->display();
    }

    function goods(){
        //查看用户信息是否存在
        if(!$_REQUEST['goods_id']){
            $this ->error('商品id不能为空');
        }
        //大师宝阁信息
        $where = array('goods.status'=>1, 'goods.id'=>$_REQUEST['goods_id'], 'master.status'=>1,'goods.frame'=>1);
        $field = 'goods.id as goods_id, goods.master_id, goods.goods_name, goods.picture, goods.price, goods.goods_info, goods.integral, goods.freight, goods.goods_pic, master.nickname, master.head_pic, master.field_id, master.auth_status, master.social_id';
        $goods = $this ->findGoods($where, $field);

        if(!$goods){
            $this ->error('商品信息有误');
        }
        //goods.picture, goods.goods_pic, master.head_pic, master.field_id, master.social_id
        $head_pic = $this ->searchPhoto($goods['head_pic']);
        $picture = $this ->searchPhoto($goods['goods_pic']);
        $goods['head_pic'] = $head_pic?$head_pic:C("API_URL").'/Uploads/Member/default.png';
        $goods['goods_pic']  = $picture?$picture:'';
        if($goods['social_id'] != 0){
            $goods['social_status'] = '1';
        }else{
            $goods['social_status'] = '2';
        }
        if(!empty($goods['field_id'])){
            $field_id = explode(',',$goods['field_id']);
            $field_info = array();
            foreach($field_id as $k => $v){
                unset($field_name);
                $field_name = $this ->easyMysql('Field',3,array('id'=>$v,'status'=>array('neq',9)),'','id as field_id, field_name');
                if(!empty($field_name)){
                    $field_info[] = $field_name;
                }
            }
        }else{
            $field_info = array();
        }

        $this ->assign('field_info',$field_info);

        if(!empty($goods['picture'])){
            $goods_pic = explode(',',$goods['picture']);
            $pic_info = array();
            foreach($goods_pic as $k => $v){
                unset($pic);
                $pic = $this ->searchPhoto($v);
                $pic_info[$k]['picture'] = $pic?$pic:'';
            }
        }else{
            $pic_info = array();
        }
        $this ->assign('pic_info',$pic_info);
//        $goods['picture'] = $pic_info;

        $order_num = $this ->getOrderNum($goods['goods_id']);
        $goods['order_num'] = $order_num?$order_num.'':'0';
        //查看用户是否收藏该商品
//        if($member){
//            $where = array('goods_id'=>$_REQUEST['goods_id'],'m_id'=>$member['id'],'status'=>array('neq',9));
//            $collect = $this ->easyMysql('Collect','3',$where);
//            if($collect){
//                $goods['collect'] = '1';
//            }else{
//                $goods['collect'] = '2';
//            }
//        }else{
//            $goods['collect'] = '2';
//        }
        //查看商品评价
        $where = array('comment.goods_id'=>$_REQUEST['goods_id'],'comment.status'=>array('neq',9));
        $field = 'comment.id as comment_id, comment.evaluate_star, comment.content, comment.create_time, member.head_pic, member.nickname, comment.content_pic';
        $order = 'comment.create_time desc';
        $comment = $this ->selectComment($where, $field, $order, 3);
        if(!$comment){
            $comment = array();
            $comment_num = '0';
        }else{
            $comment_num = count($comment);
            foreach($comment as $k => $v){
                unset($pic);
                unset($head_pic);
                $head_pic = $this ->searchPhoto($v['head_pic']);
                $comment[$k]['head_pic'] = $head_pic?$head_pic:C('API_URL').'/Uploads/Member/default.png';
                $comment[$k]['create_time'] = date('Y-m-d',$v['create_time']);
                if($v['content_pic'] != ''){
                    $picture = explode(',',$v['content_pic']);
                    $pic = array();
                    foreach($picture as $key => $val){
                        unset($photo);
                        $photo = $this ->searchPhoto($val);
                        $pic[$key]['picture'] = $photo?$photo:'';
                    }
                    $comment[$k]['picture'] = $pic;
                }else{
                    $comment[$k]['picture'] = array();
                }
            }
        }
        $this ->assign('comment',$comment);
//        $goods['comment'] = $comment;
        $goods['comment_num'] = $comment_num;
        $this ->assign('goods',$goods);
        $this->display();
    }
}