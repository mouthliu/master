<?php
namespace Api\Logic;
/**
 * Class IndexLogic
 * @package Api\Logic
 * 首页模块
 */
class IndexLogic extends BaseLogic{
    /**
     * 首页接口
     */
    public function indexPage($request = array()){
        if($request['token']){
            $member = $this ->searchMember($request['token']);
        }
        //获取轮播图
        $advert = $this ->easyMysql('Advert','4',array('type'=>1,'status'=>1),'','id as advert_id, ad_pic, url','sort desc, create_time desc');
        if(!$advert){
            $advert = array();
        }else{
            foreach($advert as $k =>$v){
                $advert[$k]['ad_pic'] = $this ->searchPhoto($v['ad_pic']);
            }
        }
        $result['advert'] = $advert;
        //获取服务列表   只获取9条
        $service = $this ->easyMysql('Service','4',array('status'=>1,'is_show'=>1),'','id as service_id, title, picture','sort desc, create_time desc','','9');
        if(!$service){
            $service = array();
        }else{
            foreach($service as $k =>$v){
                $service[$k]['picture'] = $this ->searchPhoto($v['picture']);
            }
        }
        $result['service'] = $service;
        //获取推荐大师
        $where = array('is_recommend'=>1,'status'=>1);
        $field = 'id as master_id, nickname, introduction, head_pic, field_id, social_id, auth_status';
        $order = 'sort desc, create_time desc';
        $master = $this ->easyMysql('Master','4',$where,'',$field, $order,'',3);
        if(!$master){
            $master = array();
        }else{
            foreach($master as $k => $v){
                //获取大师的头像
                $head_pic = $this ->searchPhoto($v['head_pic']);
                $master[$k]['head_pic'] = $head_pic?$head_pic:C('API_URL').'/Uploads/Master/default.png';
                //获取大师的协会状态
                if($v['social_id'] != 0){
                    $where = array('id'=>$v['social_id'],'status'=>array('neq',9));
                    $social_name = $this ->easyMysql('Social','3',$where);
                    if($social_name){
                        $master[$k]['social_status'] = '1';
                    }else{
                        $master[$k]['social_status'] = '2';
                    }
                }else{
                    $master[$k]['social_status'] = '2';
                }
                //获取大师的标签
                $field_list = array();
                if(!empty($v['field_id'])){
                    $field_info = explode(',',$v['field_id']);
                    foreach($field_info as $key =>$val){
                        $where = array('id'=>$val,'status'=>array('neq',9));
                        $field_name = $this ->easyMysql('Field','3',$where,'','id as field_id, field_name');
                        $field_list[] = $field_name?$field_name:'';
                    }
                }
                $master[$k]['field_list'] = $field_list?$field_list:array();
            }
        }
        $result['master'] = $master;
        //获取风水文创
        $where = array('goods.status'=>1,'goods.is_show'=>1);
        $field = 'goods.id as goods_id, goods.master_id, goods.goods_name, goods.price, goods.goods_pic, master.nickname, master.head_pic, master.auth_status, master.social_id';
        $order = 'goods.sort desc, goods.create_time desc';
        $goods = D('Goods') ->selectGoods($where, $field, $order, 3);
        if(!$goods){
            $goods = array();
        }else{
            foreach($goods as $k => $v){
                unset($picture);
                if($v['social_id'] != 0){
                    $goods[$k]['social_status'] = '1';
                }else{
                    $goods[$k]['social_status'] = '2';
                }
                $head_pic = $this ->searchPhoto($v['head_pic']);
                $goods[$k]['head_pic'] = $head_pic?$head_pic:C('API_URL').'/Uploads/Master/default.png';
                $picture = $this ->searchPhoto($v['goods_pic']);
                $goods[$k]['goods_pic'] = $picture?$picture:'';
                $goods_num = $this ->getOrderNum($v['goods_id']);
                $goods[$k]['order_num'] = $goods_num?$goods_num.'':'0';
                $goods[$k]['price'] = $this ->goodsPrice($v['price'],1);
            }
        }

        $result['goods'] = $goods;

        //获取热门资讯
        $where = array('news.status'=>1,'news.is_hot'=>1);
        $field = 'news.id as news_id, news.title, news.picture, news.browse_times, news.create_time, master.nickname';
        $order = 'news.sort desc, news.create_time desc';
        $limit = 3;
        $news = D('News') ->selectNews($where, $field, $order, $limit);
        if(!$news){
            $news = array();
        }else{
            foreach($news as $k => $v){
                $news_pic = $this ->searchPhoto($v['picture']);
                $news[$k]['news_pic'] = $news_pic?$news_pic:'';
                $news[$k]['create_time'] = date('Y.m.d',$v['create_time']);
            }
        }
        $result['news'] = $news;

        //获取每日运势的数据
        unset($where);
        if(!$member){
            $month = date('m.d',time());
        }else{
            $where['m_id'] = $member['id'];
            $where['date'] = date('Y-m-d');
            $where['status'] = 1;
            $res_fortune = $this ->easyMysql('Fortune','3',$where);
            if($res_fortune){
                $res_data['today_star'] = $res_fortune['today_star'];
                $res_data['today_fortune'] = $res_fortune['today_fortune'];
            }else{
                if($member['month'] != ''){
                    $month = $member['month'];
                }else{
                    $month = date('m.d',time());
                }
            }
        }

        if($month>=3.21 && $month<=4.19){
            $keyword =  '1';
        }elseif($month>=4.20 && $month<=5.20){
            $keyword =  '2';
        }elseif($month>=5.21 && $month<=6.21){
            $keyword =  '3';
        }elseif($month>=6.22 && $month<=7.22){
            $keyword =  '4';
        }elseif($month>=7.23 && $month<=8.22){
            $keyword =  '5';
        }elseif($month>=8.23 && $month<=9.22){
            $keyword =  '6';
        }elseif($month>=9.23 && $month<=10.23){
            $keyword =  '7';
        }elseif($month>=10.24 && $month<=11.22){
            $keyword =  '8';
        }elseif($month>=11.23 && $month<=12.21){
            $keyword =  '9';
        }elseif($month>=12.22 && $month<=1.19){
            $keyword =  '10';
        }elseif($month>=1.20 && $month<=2.18){
            $keyword =  '11';
        }elseif($month>=2.19 && $month<=3.20){
            $keyword =  '12';
        }

        $fortune['type'] = 4;
        $fortune['keyword'] = $keyword;

        if(!$res_data){
            $fortune['api_type'] = 1;
            $today_fortune = $this ->fortuneTelling($fortune);

            $res_data['today_star'] = $today_fortune['today']['summary'];
            $res_data['today_fortune'] = $today_fortune['today']['presummary'];
            if($member){
                $res_data['m_id'] = $member['id'];
                $res_data['create_time'] = time();
                $res_data['date'] = date('Y-m-d',time());
                $res_data['status'] = 1;
                $this ->easyMysql('Fortune',1,'',$res_data);
            }
        }
        $result['today_star'] = $res_data['today_star'];
        $result['today_fortune'] = $res_data['today_fortune'];

        unset($where);
        unset($res_data);
        if($member){
            $where['m_id'] = $member['id'];
            $where['date'] = date('Y-m-d');
            $where['status'] = 2;
            $calender = $this ->easyMysql('Fortune',3,$where);
            if($calender){
                $res_data['good'] = $calender['good'];
                $res_data['done'] = $calender['done'];
            }
        }

        if(!$calender){
            $can['api_type'] = 1;
            $calender = $this ->calendar($can);
            $res_data['good'] = implode(',',$calender['today']['yi']);
            $res_data['done'] = implode(',',$calender['today']['ji']);
            if($member){
                $res_data['m_id'] = $member['id'];
                $res_data['create_time'] = time();
                $res_data['date'] = date('Y-m-d');
                $res_data['status'] = 2;
                $this ->easyMysql('Fortune',1,'',$res_data);
            }
        }

        $result['good'] = explode(',',$res_data['good']);
        $result['down'] = explode(',',$res_data['done']);
        if(empty($res_data['good'])){
            $result['good'] = array();
        }
        if(empty($res_data['done'])){
            $result['down'] = array();
        }
        apiResponse('1','',$result);
    }

    /**
     * 各种算命
     * type  1  姓名测试  2  号码测试  3  情感自测  4  星座运势  5  解梦  6  前世今生
     */
    public function fortuneTelling($request = array()){
        $appcode = '20bab2f4ab6b47dba89d151cf9f2a501';
        $keyword = $request['keyword']?$request['keyword']:'';
        $method = "GET";

        if($request['type'] == 5){
            $host = "http://jisudream.market.alicloudapi.com";
            $path = "/dream/search";
            $headers = array();
            array_push($headers, "Authorization:APPCODE " . $appcode);
            $querys = "keyword=".$keyword;
            $bodys = "";
            $url = $host . $path . "?" . $querys;
        }elseif($request['type'] == 2){
            $host = "http://jisusjhmjx.market.alicloudapi.com";
            $path = "/mobileluck/query";
            $headers = array();
            array_push($headers, "Authorization:APPCODE " . $appcode);
            $querys = "mobile=".$keyword;
            $bodys = "";
            $url = $host . $path . "?" . $querys;
        }elseif($request['type'] == '4'){
            switch($request['keyword']){
                case '白羊座': $keyword = 1; break;
                case '金牛座': $keyword = 2; break;
                case '双子座': $keyword = 3; break;
                case '巨蟹座': $keyword = 4; break;
                case '狮子座': $keyword = 5; break;
                case '处女座': $keyword = 6; break;
                case '天秤座': $keyword = 7; break;
                case '天蝎座': $keyword = 8; break;
                case '射手座': $keyword = 9; break;
                case '摩羯座': $keyword = 10; break;
                case '水瓶座': $keyword = 11; break;
                case '双鱼座': $keyword = 12; break;
            }
            $host = "http://jisuastro.market.alicloudapi.com";
            $path = "/astro/fortune";
            $headers = array();
            array_push($headers, "Authorization:APPCODE " . $appcode);
            $querys = "astroid=".$keyword."&date=".date('Y-m-d');
            $bodys = "";
            $url = $host . $path . "?" . $querys;
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $result = curl_exec($curl);
        $res = strstr($result,'{');

        $res_data = json_decode($res, true);

        if($request['api_type'] == 1){
            return $res_data['result'];
        }

        apiResponse('1','',$res_data['result']);
    }

    /**
     * 签到
     */
    public function sign($request = array()){
        $member = $this ->searchMember($request['token']);
        $time = strtotime(date('Y-m-d',time()));
        $where = array('m_id'=>$member['id'],'stop_time'=>$time);
        $res = $this ->easyMysql('SignRecord','3',$where);
        $where = array('m_id'=>$member['id'],'sign_time'=>$time);
        $res_data = $this ->easyMysql('SignRecord','3',$where);
        if($res_data){
//            $result_data['sign_num']=$res_data['sign_num'];
//            $result_data['integral']=$signday['integral'];
//            $result_data['content']=$signday['content'];
//            $result_data['tomorrow_integral']=$res_data['sign_num'];

            $result = $this ->easyMysql('Sign','4','','','','day desc');
            if($res_data['sign_num'] >= $result[0]['day']){
                $result_data['sign_num'] = $res_data['sign_num'].'';
                $result_data['integral'] = $result[0]['integral'];
                $result_data['tomorrow_integral'] = $result[0]['integral'];
                $result_data['content']  = $result[0]['content'];
            }else{
                foreach($result as $k =>$v){
                    if($k == 0){
                        continue;
                    }
                    if($res_data['sign_num'] == $v['day']){
                        $result_data['sign_num'] = $res_data['sign_num'];
                        $result_data['integral'] = $v['integral'];
                        $result_data['content']  = $v['content'];
                        $result_data['tomorrow_integral'] = $result[($k-1)]['integral'];
                    }
                }
            }

            $result_data['status']=2;
            apiResponse('1','',$result_data);
        }

        $data['m_id'] = $member['id'];
        $data['sign_time'] = $time;
        $data['stop_time'] = $time + 86400;
        if($res){
            $data['sign_num'] = $res['sign_num'] + 1;
        }else{
            $data['sign_num'] = 1;
        }
        $data['create_time'] = time();
        $res_data = $this ->easyMysql('SignRecord','1','',$data);
        if(!$res_data){
            apiResponse('0','签到失败');
        }

        $result = $this ->easyMysql('Sign','4','','','','day desc');
        if($data['sign_num'] >= $result[0]['day']){
            $result_data['sign_num'] = $data['sign_num'].'';
            $result_data['integral'] = $result[0]['integral'];
            $result_data['tomorrow_integral'] = $result[0]['integral'];
            $result_data['content']  = $result[0]['content'];
        }else{
            foreach($result as $k =>$v){
                if($k == 0){
                    continue;
                }
                if($data['sign_num'] == $v['day']){
                    $result_data['sign_num'] = $data['sign_num'];
                    $result_data['integral'] = $v['integral'];
                    $result_data['content']  = $v['content'];
                    $result_data['tomorrow_integral'] = $result[($k-1)]['integral'];
                }
            }
        }
        $result_data['status']=1;
        $integral = $this ->addIntegral($member['id'], 1, '签到得积分', 1, $result_data['integral']);
        $member_integral = $this ->setType('Member',array('id'=>$member['id']),'integral',$result_data['integral'],1);
        apiResponse('1','',$result_data);
    }

    /**
     * 商品列表
     * 商品名称   goods_name
     * 商品类别   goods_type_id
     * 排序方式   order  1  综合排序  2  满意度从高到低  3  满意度从低到高  4  价格从高到低  5  价格从低到高
     * 价格区间   min_price   max_price
     * 评分范围   min_score   max_score
     * 新晋资深搜索  status   1  新晋  2  资深
     */
    public function goodsList($request = array()){
        //商品名称
        if($request['goods_name']){
            $where['goods.goods_name'] = array('like','%'.$request['goods_name'].'%');
        }
        //商品类别
//        if($request['goods_type_id']){
//            $where['goods.goods_type'] = $request['goods_type_id'];
//        }

        //擅长领域搜索
        if($request['goods_type_id']){
            $where['_string'] = " ( goods.goods_type = ".$request['goods_type_id'].") OR ( goods.goods_type like '%,".$request['goods_type_id'].",%') OR ( goods.goods_type like '%,".$request['goods_type_id']."') OR ( goods.goods_type like '".$request['goods_type_id'].",%' )";
        }

        //价格区间
        if(!empty($request['min_price']) &&!empty($request['max_price'])){
            $min_price = $request['min_price'];
            $max_price = $request['max_price'];
            $where['goods.price'] = array('between',"$min_price,$max_price");
        }elseif(!empty($request['min_price'])){
            $where['goods.price'] = array('egt',$request['min_price']);
        }elseif(!empty($request['max_price'])){
            $where['goods.price'] = array('elt',$request['max_price']);
        }
        //评分范围
        if(!empty($request['min_score']) &&!empty($request['max_score'])){
            $min_score = $request['min_score'];
            $max_score = $request['max_score'];
            $where['goods.degree'] = array('between',"$min_score,$max_score");
        }elseif(!empty($request['min_score'])){
            $where['goods.degree'] = array('egt',$request['min_score']);
        }elseif(!empty($request['max_score'])){
            $where['goods.degree'] = array('elt',$request['max_score']);
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

        if($request['order']){
            switch($request['order']){
                case 1 : $order = 'goods.sort desc, goods.create_time desc'; break;
                case 2 : $order = 'goods.degree desc, goods.sort desc, goods.create_time desc'; break;
                case 3 : $order = 'goods.degree asc, goods.sort desc, goods.create_time desc'; break;
                case 4 : $order = 'goods.price desc, goods.sort desc, goods.create_time desc'; break;
                case 5 : $order = 'goods.price asc, goods.sort desc, goods.create_time desc'; break;
                default : $order = 'goods.sort desc, goods.create_time desc';
            }
        }else{
            $order = 'goods.sort desc, goods.create_time desc';
        }

        $where['goods.status'] = 1;
        $where['goods.frame']  = 1;
        $field = 'goods.id as goods_id, goods.goods_name, goods.price, goods.goods_pic, master.nickname, master.head_pic, master.auth_status, master.social_id';
        $result = D('Goods') ->selectGoods($where, $field, $order, '', $request['p']);
        if(!$result){
            $result = array();
        }else{
            foreach($result as $k => $v){
                unset($goods_pic);
                unset($head_pic);
                $goods_pic = $this ->searchPhoto($v['goods_pic']);
                $result[$k]['goods_pic'] = $goods_pic?$goods_pic:'';
                $head_pic = $this ->searchPhoto($v['head_pic']);
                $result[$k]['head_pic'] = $head_pic?$head_pic:C('API_URL').'Uploads/Master/default.png';

                if($v['social_id'] != 0){
                    $result[$k]['social_status'] = '1';
                }else{
                    $result[$k]['social_status'] = '2';
                }
                $goods_num = $this ->getOrderNum($v['goods_id']);
                $result[$k]['order_num'] = $goods_num?$goods_num.'':'0';
                $result[$k]['price'] = $this ->goodsPrice($v['price'],1);
            }
        }

        apiResponse('1','',$result);
    }

    /**
     * 商品类别列表
     */
    public function goodsTypeList($request = array()){
        $where = array('parent_id'=>0,'status'=>1);
        $field = 'id as parent_id, type_name';
        $order = 'sort desc, create_time desc';
        $parent = $this ->easyMysql('GoodsType','4',$where,'',$field,$order);
        if(!$parent){
            apiResponse('1','',array());
        }
        $result['parent'] = $parent;
        unset($where);
        unset($field);
        if(empty($request['parent_id'])){
            $where['parent_id'] = $parent[0]['parent_id'];
        }else{
            $where['parent_id'] = $request['parent_id'];
        }
        $where['status'] = 1;
        $field = 'id as goods_type_id, type_name';
        $goods_type = $this ->easyMysql('GoodsType','4',$where,'',$field,$order);
        if(!$goods_type){
            $goods_type = array();
        }
        $result['goods_type'] = $goods_type;

        apiResponse('1','',$result);
    }

    /**
     * 日历表
     */
    public function calendar($request = array()){

        $host = "http://jisuhlcx.market.alicloudapi.com";
        $path = "/huangli/date";
        $method = "GET";
        $appcode = "20bab2f4ab6b47dba89d151cf9f2a501";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "day=".date('d')."&month=".date('m')."&year=".date('Y');
        $bodys = "";
        $url = $host . $path . "?" . $querys;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $result_info = curl_exec($curl);
        $res = strstr($result_info,'{');
        $res_data = json_decode($res, true);
        $today = $res_data['result'];
        $result_data['today'] = $today;

        $where = array('m_s.service_id'=>3,'master.is_recommend'=>1,'m_s.status'=>array('neq',9),'master.status'=>1);
        $field = 'master.id as master_id, master.nickname, master.head_pic, master.field_id, master.auth_status, master.social_id, master.introduction, master.score';
        $order = 'master.sort desc, master.create_time desc';
        $master = D('Master') ->typeMaster($where, $field, $order);
        if(!$master){
            $master = array();
        }else{
            $master = D('Master') ->foreachMaster($master);
        }
        $result_data['master'] = $master;

        if($request['api_type'] == 1){
            return $result_data;
        }

        apiResponse('1','',$result_data);
    }

    /**
     * 月历表
     */
    public function monthCalendar($request = array()){
        D('Index','Logic') ->monthCalendar(I('post.'));
    }

    /**
     * 今日运势
     */
    public function fortuneToday($request = array()){
        if($request['token']){
            $member = $this ->searchMember($request['token']);
            $month = $member['month']?$member['month']:date('m.d',time());
            $result['name'] = $member['name']?$member['name']:$member['nickname'];
            $result['birthday'] = $member['birthday']?$member['birthday']:'';
        }else{
            $month = date('m.d',time());
            $result['name'] = '';
            $result['birthday'] = '';
        }

        if($month>=3.21 && $month<=4.19){
            $keyword =  '1';
        }elseif($month>=4.20 && $month<=5.20){
            $keyword =  '2';
        }elseif($month>=5.21 && $month<=6.21){
            $keyword =  '3';
        }elseif($month>=6.22 && $month<=7.22){
            $keyword =  '4';
        }elseif($month>=7.23 && $month<=8.22){
            $keyword =  '5';
        }elseif($month>=8.23 && $month<=9.22){
            $keyword =  '6';
        }elseif($month>=9.23 && $month<=10.23){
            $keyword =  '7';
        }elseif($month>=10.24 && $month<=11.22){
            $keyword =  '8';
        }elseif($month>=11.23 && $month<=12.21){
            $keyword =  '9';
        }elseif($month>=12.22 && $month<=1.19){
            $keyword =  '10';
        }elseif($month>=1.20 && $month<=2.18){
            $keyword =  '11';
        }elseif($month>=2.19 && $month<=3.20){
            $keyword =  '12';
        }

        $host = "http://jisuastro.market.alicloudapi.com";
        $path = "/astro/fortune";
        $method = "GET";
        $appcode = "20bab2f4ab6b47dba89d151cf9f2a501";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "astroid=".$keyword."&date=".date('Y-m-d',time());
        $bodys = "";
        $url = $host . $path . "?" . $querys;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        if (1 == strpos("$".$host, "https://"))
        {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }

        $result_info = curl_exec($curl);
        $res = strstr($result_info,'{');
        $res_data = json_decode($res, true);
        $today = $res_data['result']['today'];
        $result['today'] = $today;
        $where = array('m_s.service_id'=>10,'master.is_recommend'=>1,'m_s.status'=>array('neq',9),'master.status'=>1);
        $field = 'master.id as master_id, master.nickname, master.head_pic, master.field_id, master.auth_status, master.social_id, master.introduction, master.score';
        $order = 'master.sort desc, master.create_time desc';
        $master = D('Master') ->typeMaster($where, $field, $order);
        if(!$master){
            $master = array();
        }else{
            $master = D('Master') ->foreachMaster($master);
        }
        $result['master'] = $master;
        apiResponse('1','',$result);
    }

    /**
     * 本周运势
     */
    public function fortuneWeek($request = array()){
        $time = array();
        $time[] = mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y"));
        $time[] = mktime(0, 0 , 0,date("m"),date("d")-date("w")+2,date("Y"));
        $time[] = mktime(0, 0 , 0,date("m"),date("d")-date("w")+3,date("Y"));
        $time[] = mktime(0, 0 , 0,date("m"),date("d")-date("w")+4,date("Y"));
        $time[] = mktime(0, 0 , 0,date("m"),date("d")-date("w")+5,date("Y"));
        $time[] = mktime(0, 0 , 0,date("m"),date("d")-date("w")+6,date("Y"));
        $time[] = mktime(0, 0 , 0,date("m"),date("d")-date("w")+7,date("Y"));

        if($request['token']){
            $member = $this ->searchMember($request['token']);
            $month = $member['month']?$member['month']:date('m.d',time());
            $result['name'] = $member['name']?$member['name']:$member['nickname'];
            $result['birthday'] = $member['birthday']?$member['birthday']:'';
        }else{
            $month = date('m.d',time());
            $result['name'] = '';
            $result['birthday'] = '';
        }

        if($month>=3.21 && $month<=4.19){
            $keyword =  '1';
        }elseif($month>=4.20 && $month<=5.20){
            $keyword =  '2';
        }elseif($month>=5.21 && $month<=6.21){
            $keyword =  '3';
        }elseif($month>=6.22 && $month<=7.22){
            $keyword =  '4';
        }elseif($month>=7.23 && $month<=8.22){
            $keyword =  '5';
        }elseif($month>=8.23 && $month<=9.22){
            $keyword =  '6';
        }elseif($month>=9.23 && $month<=10.23){
            $keyword =  '7';
        }elseif($month>=10.24 && $month<=11.22){
            $keyword =  '8';
        }elseif($month>=11.23 && $month<=12.21){
            $keyword =  '9';
        }elseif($month>=12.22 && $month<=1.19){
            $keyword =  '10';
        }elseif($month>=1.20 && $month<=2.18){
            $keyword =  '11';
        }elseif($month>=2.19 && $month<=3.20){
            $keyword =  '12';
        }

        $host = "http://jisuastro.market.alicloudapi.com";
        $path = "/astro/fortune";
        $method = "GET";
        $appcode = "20bab2f4ab6b47dba89d151cf9f2a501";
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $res_info = array();
        foreach($time as $k => $v){
            $querys = "astroid=".$keyword."&date=".date('Y-m-d',$v);
            $bodys = "";
            $url = $host . $path . "?" . $querys;

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_FAILONERROR, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, true);
            if (1 == strpos("$".$host, "https://"))
            {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            }

            $result_info = curl_exec($curl);
            $res = strstr($result_info,'{');
            $res_data = json_decode($res, true);
            $res_info[] = $res_data['result']['today'];
        }
        $result['res_info'] = $res_info;

        $where = array('m_s.service_id'=>10,'master.is_recommend'=>1,'m_s.status'=>array('neq',9),'master.status'=>1);
        $field = 'master.id as master_id, master.nickname, master.head_pic, master.field_id, master.auth_status, master.social_id, master.introduction, master.score';
        $order = 'master.sort desc, master.create_time desc';
        $master = D('Master') ->typeMaster($where, $field, $order);
        if(!$master){
            $master = array();
        }else{
            $master = D('Master') ->foreachMaster($master);
        }
        $result['master'] = $master;
        apiResponse('1','',$result);
    }
}