<?php
namespace Api\Logic;
/**
 * Class IndexLogic
 * @package Api\Logic
 * 首页模块
 */
class MasterServiceLogic extends BaseLogic{
    /**
     * 大师列表——这个列表得改
     * 大师昵称搜索   nickname
     * 服务分类搜索   service_id
     * 擅长领域搜索   field_id
     * 官方认证搜索   auth_status
     * 协会认证搜索   social_status
     * 价格区间搜索   min_price   max_price
     * 位置搜索？？
     * 评分范围搜索   min_score   max_price
     * 地区位置搜索   city_id
     * 认证资质搜索   status  1  资深   2   新晋
     * 综合排序搜索   order  1满意度从高到低  2满意度从低到高  3价格从高到低  4价格从低到高  5距离从远到近  6距离从近到远  7新晋  8资深  9  综合排序
     */
    public function masterList($request = array()){
        //获取服务类别
        $where = array('status'=>array('neq',9));
        $field = 'id as service_id, title';
        $order = 'create_time asc';
        $service = $this ->easyMysql('Service', '4', $where, '', $field, $order);
        if(!$service){
            $service = array();
        }
        $row['service_id'] = '0';
        $row['title'] = '全部分类';
        array_unshift($service,$row);

        $result['service'] = $service;
        //获取擅长领域类别
        $field = 'id as field_id, field_name';
        $order = 'sort asc, create_time asc';
        $field_list = $this ->easyMysql('Field', '4', $where, '', $field, $order);
        if(!$field_list){
            $field_list = array();
        }
        $result['field'] = $field_list;
        unset($where);
        //大师昵称搜索
        if($request['nickname']){
            $where['master.nickname'] = array('like','%'.$request['nickname'].'%');
        }
        //服务分类搜索
        if($request['service_id']){
            $where['m_s.service_id'] = $request['service_id'];
            //价格区间搜索
            if(!empty($request['min_price']) &&!empty($request['max_price'])){
                $min_price = $request['min_price'];
                $max_price = $request['max_price'];
                $where['m_s.price'] = array('between',"$min_price,$max_price");
            }elseif(!empty($request['min_price'])){
                $min_price = $request['min_price'];
                $where['m_s.price'] = array('egt',$min_price);
            }elseif(!empty($request['max_price'])){
                $max_price = $request['max_price'];
                $where['m_s.price'] = array('elt',$max_price);
            }else{

            }
        }
        //擅长领域搜索
        if($request['field_id']){
            $where['_string'] = " ( master.field_id = ".$request['field_id'].") OR ( master.field_id like '%,".$request['field_id'].",%') OR ( master.field_id like '%,".$request['field_id']."') OR ( master.field_id like '".$request['field_id'].",%' )";
        }
        //所在城市搜索
        if($request['city_id']){
            $where['master.city'] = $request['city_id'];
        }
        //官方认证搜索
        if($request['auth_status']){
            $where['master.auth_status'] = 3;
        }
        //协会认证搜索
        if($request['social_status']){
            $where['master.social_id'] = array('neq','0');
        }

        //评分范围搜索
        if(!empty($request['min_score']) &&!empty($request['max_score'])){
            $min_score = $request['min_score'];
            $max_score = $request['max_score'];
            $where['master.score'] = array('between',"$min_score,$max_score");
        }elseif(!empty($request['min_score'])){
            $min_score = $request['min_score'];
            $where['master.score'] = array('egt',$min_score);
        }elseif(!empty($request['max_score'])){
            $max_score = $request['max_score'];
            $where['master.score'] = array('elt',$max_score);
        }else{

        }
        //新晋资深搜索
        if(!empty($request['status'])){
            $time = strtotime(date('Y-m-d',time())) - (90 * 86400);
            if($request['status'] == 1){
                $where['master.auth_time'] = array('gt',$time);
            }else{
                $where['master.auth_time'] = array('elt',$time);
            }
        }

        //综合排序搜索  满意度从高到低  满意度从低到高  价格从高到低  价格从低到高  距离从远到近  距离从近到远  新晋  资深
        if($request['service_id']){
            if($request['order']){
                switch($request['order']){
                    case 1: $order = 'master.score desc, master.create_time desc'; break;
                    case 2: $order = 'master.score asc, master.create_time desc'; break;
                    case 3: $order = 'm_s.price desc, master.create_time desc'; break;
                    case 4: $order = 'm_s.price desc, master.create_time desc'; break;
                    case 5: $order = 'distance desc, master.create_time desc'; break;
                    case 6: $order = 'distance asc, master.create_time desc'; break;
                    case 7: $order = 'master.auth_time asc, master.create_time desc'; break;
                    case 8: $order = 'master.auth_time desc, master.create_time desc'; break;
                    default: $order = 'master.create_time desc';
                }
            }else{
                $order = 'master.create_time desc';
            }
        }else{
            if($request['order']){
                switch($request['order']){
                    case 1: $order = 'master.score desc, master.create_time desc'; break;
                    case 2: $order = 'master.score asc, master.create_time desc'; break;
                    case 3: $order = 'master.create_time desc'; break;
                    case 4: $order = 'master.create_time desc'; break;
                    case 5: $order = 'distance desc, master.create_time desc'; break;
                    case 6: $order = 'distance asc, master.create_time desc'; break;
                    case 7: $order = 'master.auth_time asc, master.create_time desc'; break;
                    case 8: $order = 'master.auth_time desc, master.create_time desc'; break;
                    default: $order = 'master.create_time desc';
                }
            }else{
                $order = 'master.create_time desc';
            }
        }

        $earthRadius = 6367000;
        $lng = $request['lng']?$request['lng']:117.159644;
        $lat = $request['lat']?$request['lat']:39.098411;
        if($request['service_id']){
            $field = "master.id as master_id, master.nickname, master.head_pic, master.field_id, master.introduction, master.score, master.auth_status, master.social_id, m_s.price, ROUND($earthRadius*2*asin(sqrt(pow(sin((lat*PI()/180-$lat*PI()/180)/2),2)+cos($lat*PI()/180)*cos(lat*PI()/180)*pow(sin((lng*PI()/180-$lng*PI()/180)/2),2)))) as distance";
            $master = D('Master') ->typeMaster($where, $field, $order, $request['p']);
        }else{
            $field = "master.id as master_id, master.nickname, master.head_pic, master.field_id, master.introduction, master.score, master.auth_status, master.social_id, ROUND($earthRadius*2*asin(sqrt(pow(sin((lat*PI()/180-$lat*PI()/180)/2),2)+cos($lat*PI()/180)*cos(lat*PI()/180)*pow(sin((lng*PI()/180-$lng*PI()/180)/2),2)))) as distance";
            $master = D('Master') ->showMaster($where, $field, $order, $request['p']);
        }

        if(!$master){
            $master = array();
        }else{
            $master = D('Master') ->foreachMaster($master);
            foreach($master as $k => $v){
                //新晋资深搜索
                if(!empty($request['status'])){
                    if($request['status'] == 1){
                        if($v['order_num'] < 100){
                            unset($master[$k]);
                        }
                    }else{
                        if($v['order_num'] > 100){
                            unset($master[$k]);
                        }
                    }
                }

                if(isset($v['price'])){
                    $master[$k]['price'] = $this ->goodsPrice($v['price'],2);
                }
            }
        }

        $master = array_values($master);
        $result['master'] = $master;
        apiResponse('1','',$result);
    }

    /**
     * 大师详情
     */
    public function masterInfo($request = array()){
        //获取用户信息   以及是否关注大师
        if($request['token']){
            $member = $this ->searchMember($request['token']);
            $where  = array('master_id'=>$request['master_id'],'m_id'=>$member['id'],'status'=>array('neq',9));
            $follow = $this ->easyMysql('Follow',3,$where);
            if($follow){
                $follow_type = '1';
            }else{
                $follow_type = '2';
            }

        }else{
            $follow_type = '2';
        }
        //获取大师相关信息
        $where = array('id'=>$request['master_id'],'status'=>1);
        $field = 'id as master_id, nickname, head_pic, field_id, auth_status, social_id, score, introduction, easemob_account as master_easemob_account';
        $master = $this ->easyMysql('Master','3',$where,'',$field);
        if(!$master){
            apiResponse('0','大师信息有误');
        }
        $head_pic = $this ->searchPhoto($master['head_pic']);
        $master['head_pic'] = $head_pic?$head_pic:C("API_URL").'/Uploads/Master/default.png';
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
        $master['field_info'] = $field_info?$field_info:array();
        if($master['social_id'] != 0){
            $master['social_status'] = '1';
            $social_name = $this ->easyMysql('Social',5,array('id'=>$master['social_id'],'status'=>array('neq',9)),'','social_name');
            $master['social_name'] = $social_name?$social_name:'';
        }else{
            $master['social_status'] = '2';
            $master['social_name'] = '';
        }

        //获取大师的服务
        $where = array('m_s.master_id'=>$request['master_id'],'m_s.status'=>array('neq',9),'service.status'=>array('neq',9));
        $field = 'm_s.id as m_s_id, m_s.price, service.title, service.content, service.picture';
        $order = 'm_s.create_time desc';
        $service = D('Master') ->showService($where, $field, $order);

        if(!$service){
            $service = array();
        }else{
            foreach($service as $k => $v){
                $picture = $this ->searchPhoto($v['picture']);
                $service[$k]['picture'] = $picture?$picture:'';
                $service[$k]['price'] = $this ->goodsPrice($v['price'],2);
            }
        }
        $master['service'] = $service;
        //获取大师的评论
        $where = array('comment.master_id'=>$request['master_id'], 'comment.status'=>array('neq',9));
        $field = 'comment.id as comment_id, comment.content, comment.rank, comment.create_time, member.nickname, member.head_pic, comment.picture';
        $order = 'comment.create_time desc';
        $comment = D('MasterService') ->commentList($where, $field, $order, '', 2);
        $comment_num = D('MasterService') ->commentList($where, $field, $order, '');
        if(!$comment){
            $comment = array();
            $comment_num = '0';
        }else{
            $comment_num = count($comment_num);
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
        $master['comment'] = $comment;
        $master['comment_num'] = $comment_num?$comment_num.'':'0';
        //获取大师宝阁
        $where = array('master_id'=>$request['master_id'],'status'=>1,'frame'=>1);
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
                $goods[$k]['head_pic'] = $master['head_pic'];
                $goods[$k]['auth_status'] = $master['auth_status'];
                $goods[$k]['social_status'] = $master['social_status'];
                $goods_num = $this ->getOrderNum($v['goods_id']);
                $goods[$k]['order_num'] = $goods_num?$goods_num.'':'0';
                $goods[$k]['price'] = $this ->goodsPrice($v['price'],1);
            }
        }
        $master['goods'] = $goods;
        //获取大师的文章
        $where = array('news.master_id'=>$request['master_id'],'news.status'=>1);
        $field = 'news.id as news_id, news.title, news_type.type_name, master.nickname, news.create_time, news.browse_times, news.picture';
        $order = 'news.create_time desc';
        $news  = D('News') ->selectNews($where, $field, $order, 3);
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
        $master['news'] = $news;
        //查询大师的回答
        $where = array('answer.master_id'=>$request['master_id'],'answer.status'=>array('neq',9));
        $field = 'answer.id as answer_id, rorder.id as rorder_id, rorder.m_id ,rorder.title, rorder.reward_price, rorder.watch_man, rorder.create_time, rorder.is_anonymous, member.nickname, member.head_pic, reward.reward_name';
        $order = 'answer.create_time desc';
        $reward = D('RewardOrder') ->selectAnswerList($where, $field, $order, '', 3);
        if(!$reward){
            $reward = array();
        }else{
            foreach($reward as $k => $v){
                unset($head_pic);
                if($v['is_anonymous'] == 2){
                    $reward[$k]['head_pic'] = '';
                    $reward[$k]['nickname'] = '';
                }else{
                    $head_pic = $this ->searchPhoto($v['head_pic']);
                    $reward[$k]['head_pic'] = $head_pic?$head_pic:C('API_URL').'/Uploads/Member/default.png';
                }
                $reward[$k]['create_time'] = date('Y-m-d',$v['create_time']);

                if($member && $member['id'] == $v['m_id']){
                    $reward[$k]['rorder_type'] = '1';
                }else{
                    $reward[$k]['rorder_type'] = '2';
                }
            }
        }
        $master['reward'] = $reward;
        $master['follow'] = $follow_type;
        $order_num = $this ->serviceOrderNum($master['master_id']);
        $master['order_num'] = $order_num?$order_num.'':'0';
        $master['member_easemob_account'] = $member['easemob_account']?$member['easemob_account']:'';
        apiResponse('1','',$master);
    }

    /**
     * 评价列表
     */
    public function commentList($request = array()){
        //获取大师的评论
        $where = array('comment.master_id'=>$request['master_id'], 'comment.status'=>array('neq',9));
        $field = 'comment.id as comment_id, comment.content, comment.rank, comment.create_time, member.nickname, member.head_pic, comment.picture';
        $order = 'comment.create_time desc';
        $comment = D('MasterService') ->commentList($where, $field, $order, $request['p']);
        if(!$comment){
            $comment = array();
        }else{
            foreach($comment as $k => $v){
                unset($head_pic);
                unset($picture);
                unset($pic);
                $head_pic = $this ->searchPhoto($v['head_pic']);
                $comment[$k]['head_pic'] = $head_pic?$head_pic:'/Uploads/Member/default.png';
                $comment[$k]['create_time'] = date('Y-m-d',$v['create_time']);
                if($v['picture'] != ''){
                    $picture = explode(',',$v['picture']);
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

        apiResponse('1','',$comment);
    }

    /**
     * 商品列表
     */
    public function goodsList($request = array()){
        $where = array('goods.master_id'=>$request['master_id'],'goods.status'=>1,'frame'=>1);
        $field = 'goods.id as goods_id, goods.goods_pic, goods.goods_name, goods.price, master.nickname, master.head_pic, master.auth_status, master.social_id';
        $order = 'goods.create_time asc';
        $goods = D("Goods") ->selectGoods($where, $field, $order, '', $request['p']);

        if(!$goods){
            $goods = array();
        }else{
            foreach($goods as $k => $v){
                unset($picture);
                $picture = $this ->searchPhoto($v['goods_pic']);
                $goods[$k]['goods_pic'] = $picture?$picture:'';
                $head_pic = $this ->searchPhoto($v['head_pic']);
                $goods[$k]['head_pic'] = $head_pic?$head_pic:C('API_URL').'/Uploads/Master/default.png';
                $goods[$k]['social_status'] = $v['social_id'] == 0?'2':'1';
                $goods[$k]['order_num'] = '0';
                $goods[$k]['price'] = $this ->goodsPrice($v['price'],1);
            }
        }

        apiResponse('1','',$goods);
    }

    /**
     * 新闻列表
     */
    public function newsList($request = array()){
        //获取大师的文章
        $where = array('news.master_id'=>$request['master_id'],'news.status'=>1);
        $field = 'news.id as news_id, news.title, news_type.type_name, master.nickname, news.create_time, news.browse_times, news.picture';
        $order = 'news.create_time desc';
        $news  = D('News') ->selectNews($where, $field, $order, '', $request['p']);
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

        apiResponse('1','',$news);
    }

    /**
     * 回答列表
     */
    public function answerList($request = array()){
        if($request['token']){
            $member = $this ->searchMember($request['token']);
        }
        //查询大师的回答
        $where = array('answer.master_id'=>$request['master_id'],'answer.status'=>array('neq',9));
        $field = 'answer.id as answer_id, rorder.id as rorder_id, rorder.m_id, rorder.title, rorder.reward_price, rorder.watch_man, rorder.create_time, rorder.is_anonymous, member.nickname, member.head_pic, reward.reward_name';
        $order = 'answer.create_time desc';
        $reward = D('RewardOrder') ->selectAnswerList($where, $field, $order, $request['p']);
        if(!$reward){
            $reward = array();
        }else{
            foreach($reward as $k => $v){
                unset($head_pic);
                if($v['is_anonymous'] == 2){
                    $reward[$k]['head_pic'] = '';
                    $reward[$k]['nickname'] = '';
                }else{
                    $head_pic = $this ->searchPhoto($v['head_pic']);
                    $reward[$k]['head_pic'] = $head_pic?$head_pic:C('API_URL').'/Uploads/Member/default.png';
                }
                $reward[$k]['create_time'] = date('Y-m-d',$v['create_time']);

                if($member && $member['id'] == $v['m_id']){
                    $reward[$k]['rorder_type'] = '1';
                }else{
                    $reward[$k]['rorder_type'] = '2';
                }
            }
        }

        apiResponse('1','',$reward);
    }

    /**
     * 协会详情
     */
    public function socialInfo($request = array()){
        $where = array('id'=>$request['social_id'],'status'=>1);
        $field = 'id as social_id, social_name, social_head_pic, social_info, social_pic';
        $social = $this ->easyMysql('Social','3',$where,'',$field);
        if(!$social){
            apiResponse('0','协会信息有误');
        }
        $head_pic = $this ->searchPhoto($social['social_head_pic']);
        $social['social_head_pic'] = $head_pic?$head_pic:C('API_URL').'/Uploads/Social/default.png';
        if(!empty($social['social_pic'])){
            $social_pic = explode(',',$social['social_pic']);
            $pic = array();
            foreach($social_pic as $k =>$v){
                unset($picture);
                $picture = $this ->searchPhoto($v);
                $pic[$k]['picture'] = $picture?$picture:'';
                if($k >= 4){
                    break;
                }
            }
            $social['picture'] = $pic;
        }else{
            $social['picture'] = array();
        }

        apiResponse('1','',$social);
    }

    /**
     * 协会成员
     */
    public function socialMaster($request = array()){
        $where = array('apply.social_id'=>$request['social_id'], 'apply.apply_status'=>1, 'apply.status'=>1, 'master.status'=>array('neq',9));
        $field = 'apply.id as apply_id, apply.position, master.id as master_id, master.nickname, master.head_pic, master.auth_status, master.score, master.field_id, master.social_id';
        $order = 'apply.position asc, apply.create_time asc';

        $master = D('Social') ->selectPeople($where, $field, $order , '', $request['p']);
        if(!$master){
            $master = array();
        }else{
            $master = D('Master') ->foreachMaster($master);
        }

        apiResponse('1','',$master);
    }

    /**
     * 协会列表
     */
    public function socialList($request = array()){
        $where = array('status'=>1);
        $field = 'id as social_id, social_name';
        $order = 'create_time desc';
        $social = $this ->easyMysql('Social','4',$where,'',$field,$order,$request['p']);
        if(!$social){
            $social = array();
        }

        apiResponse('1','',$social);
    }

    /**
     * 协会图库
     */
    public function socialPicture($request = array()){
        $where = array('id'=>$request['social_id'],'status'=>1);
        $field = 'id as social_id, social_name, social_pic';
        $social = $this ->easyMysql('Social','3',$where,'',$field);
        $first_num = ($request['p'] - 1) * 15;
        $last_num = $request['p'] * 15;
        if(!empty($social['social_pic'])){
            $social_pic = explode(',',$social['social_pic']);
            $picture = array();
            foreach($social_pic as $k =>$v){
                if($k >= $first_num && $k < $last_num){
                    $pic = $this ->searchPhoto($v);
                    $picture[$k]['picture'] = $pic?$pic:'';
                }
            }
        }
        $social['picture'] = $picture?$picture:array();
        apiResponse('1','',$social);
    }

    /**
     * 服务订单
     */
    public function serviceOrder($request = array()){
        $member = $this ->searchMember($request['token']);
        $where = array('id'=>$request['m_s_id'],'status'=>array('neq',9));
        $master_service = $this ->easyMysql('MasterService',3,$where);
        if(!$master_service){
            apiResponse('0','该服务已下架');
        }
        $order_sn          = date('Ymd',time()).rand(1000000,9999999);
        $data['order_sn']  = $order_sn;
        $data['m_id']      = $member['id'];
        $data['master_id'] = $master_service['master_id'];
        $data['m_s_id']    = $request['m_s_id'];
        $data['name']      = $request['name'];
        $data['sex']       = $request['sex'];
        $data['birthday']  = $request['birthday'];
        $data['city']      = $request['city_id'];
        $data['content']   = $request['content'];
        //上传图片可以为空
        if(!empty($_FILES['ser_pic']['name'])){
            $res = api('UploadPic/upload', array(array('save_path' => 'Service')));
            foreach ($res as $value) {
                $ser_pic   = $value['id'];
                $data['ser_pic'] = $ser_pic;
            }
        }
        $data['res_price']      = $master_service['price'];
        $data['price']      = $request['price'];
        $data['create_time'] = time();
        $order = $this ->easyMysql('ServiceOrder',1,'',$data);
        if(!$order){
            apiResponse('0','下单失败');
        }
        $result['sorder_id'] = $order;
        $result['order_sn']  = $order_sn;
        $result['price']     = $request['price'];

        apiResponse('1','下单成功',$result);
    }

    /**
     * 服务订单页面
     */
    public function serviceOrderPage($request = array()){
        $member = $this ->searchMember($request['token']);
        $where  = array('serviceorder.id'=>$request['sorder_id']);
        $field  = 'master.head_pic, master.nickname, service.title, serviceorder.coupon';
        $service_order = D('MasterService') ->selectServiceOrder($where, $field, '', '', '', 1);
        if(!$service_order){
            apiResponse('0','订单信息有误');
        }

        if($service_order['coupon'] != 0){
            $where  = array('id'=>$service_order['coupon'],'m_id'=>$member['id']);
            $field  = 'id as coupon_id, satisty_price, discount_price';
            $coupon = $this ->easyMysql('MemberCoupon','3',$where,'',$field);
        }

        if($request['coupon'] && empty($coupon)){
            $where  = array('id'=>$request['coupon'],'m_id'=>$member['id'],'status'=>0);
            $field  = 'id as coupon_id, satisty_price, discount_price';
            $coupon = $this ->easyMysql('MemberCoupon','3',$where,'',$field);
            if(!$coupon){
                apiResponse('0','优惠券信息有误');
            }
        }
        $result['sorder_id'] = $request['sorder_id'];
        $result['order_sn']  = $request['order_sn'];
        $result['price']     = $request['price'];
        $result['nickname']  = $service_order['nickname'];
        $result['service_name'] = $service_order['title'];
        $head_pic = $this ->searchPhoto($service_order['head_pic']);
        $result['head_pic']  = $head_pic?$head_pic:C('API_URL').'/Uploads/Master/default.png';
        $result['coupon_id']  = $coupon?$coupon['coupon_id']:'';
        $result['satisty_price']  = $coupon?$coupon['satisty_price']:'0.00';
        $result['discount_price'] = $coupon?$coupon['discount_price']:'0.00';
        $result['balance'] = $member['balance'];
        apiResponse('1','',$result);
    }

    /**
     * 服务订单支付前接口
     */
    public function payBefore($request = array()){
        $where  = array('id'=>$request['sorder_id'],'status'=>0);
        $sorder = $this ->easyMysql('ServiceOrder',3,$where);
        if(!$sorder){
            apiResponse('0','订单信息有误');
        }
        if($request['coupon_id']){
            $where = array('id'=>$request['coupon_id'],'status'=>0);
            $coupon = $this ->easyMysql('MemberCoupon','3',$where);
            if(!$coupon){
                apiResponse('0','优惠券选择有误');
            }
            $dat['status'] = 1;
            $dat['update_time'] = time();
            $res = $this ->easyMysql('MemberCoupon','2',$where,$dat);

            $data['coupon'] = $request['coupon_id'];
        }
        $where  = array('id'=>$request['sorder_id'],'status'=>0);
        $data['price'] = $request['price'];
        $data['update_time'] = time();
        $result = $this ->easyMysql('ServiceOrder',2,$where,$data);
        if(!$result){
            apiResponse('0','修改状态失败');
        }
        apiResponse('1','');
    }
}