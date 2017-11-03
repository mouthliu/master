<?php
namespace Api\Logic;
/**
 * Class IndexLogic
 * @package Api\Logic
 * 用户端商品模块
 */
class MemberGoodsLogic extends BaseLogic{
    /**
     * 首页列表
     */
    public function GoodsList(){
        //获取轮播图
        $where = array('type'=>2,'status'=>array('neq',9));
        $field = 'id as advert_id, ad_pic, url';
        $order = 'sort desc, create_time desc';
        $advert = $this ->easyMysql('Advert','4',$where,'',$field,$order);
        if(!$advert){
            $advert = array();
        }else{
            foreach($advert as $k => $v){
                unset($picture);
                $picture = $this ->searchPhoto($v['ad_pic']);
                $advert[$k]['ad_pic'] = $picture?$picture:'';
            }
        }
        $result['advert'] = $advert;

        //获取显示的类别以及图片
        $where = array('status'=>array('neq',9),'is_show'=>1);
        $field = 'id as goods_type_id, type_name, photo';
        $order = 'sort desc, create_time desc';
        $goods_type = $this ->easyMysql('GoodsType','4',$where,'',$field,$order,'',3);
        if(!$goods_type){
            $result['goods_type'] = array();
            apiResponse('1','',$result);
        }
        foreach($goods_type as $k => $v){
            unset($picture);
            $picture = $this ->searchPhoto($v['photo']);
            $goods_type[$k]['photo'] = $picture?$picture:'';
            unset($where);
            $where['goods.status'] = 1;
            $where['master.status'] = 1;
            $where['goods.frame'] = 1;
            $where['_string'] = " ( goods.goods_type = ".$v['goods_type_id'].") OR ( goods.goods_type like '%,".$v['goods_type_id'].",%') OR ( goods.goods_type like '%,".$v['goods_type_id']."') OR ( goods.goods_type like '".$v['goods_type_id'].",%' )";

            $field = 'goods.id as goods_id, goods.goods_name, goods.goods_pic, goods.price, master.id as master_id, master.nickname, master.head_pic, master.auth_status, master.social_id';
            $order = 'goods.sort desc, goods.create_time desc';
            $goods = D('Goods') ->selectGoods($where, $field ,$order ,'4');
            if(!$goods){
                $goods = array();
            }else{
                foreach($goods as $key => $val){
                    unset($picture);
                    unset($goods_pic);
                    $picture = $this ->searchPhoto($val['goods_pic']);
                    $goods[$key]['goods_pic'] = $picture?$picture:'';
                    unset($head_pic);
                    $head_pic = $this ->searchPhoto($val['head_pic']);
                    $goods[$key]['head_pic'] = $head_pic?$head_pic:C('API_URL').'/Uploads/Master/default.png';
                    if($v['social_id'] != 0){
                        $goods[$key]['social_status'] = 1;
                    }else{
                        $goods[$key]['social_status'] = 2;
                    }
                    $goods_num = $this ->getOrderNum($val['goods_id']);
                    $goods[$key]['order_num'] = $goods_num?$goods_num.'':'0';
                    $goods[$key]['price'] = $this ->goodsPrice($val['price'],1);
                }
            }
            $goods_type[$k]['goods'] = $goods;
        }

        $result['goods_type'] = $goods_type;
        apiResponse('1','',$result);
    }

    /**
     * 首页详情
     */
    public function goodsInfo($request = array()){
        //查看用户信息是否存在
        if($request['token']){
            $member = $this ->searchMember($request['token']);
        }
        //大师宝阁信息
        $where = array('goods.status'=>1, 'goods.id'=>$request['goods_id'], 'master.status'=>1,'goods.frame'=>1);
        $field = 'goods.id as goods_id, goods.master_id, goods.goods_name, goods.picture, goods.price, goods.goods_info, goods.integral, goods.freight, goods.goods_pic, master.nickname,master.easemob_account, master.head_pic, master.field_id, master.auth_status, master.social_id';
        $goods = D('Goods') ->findGoods($where, $field);

        if(!$goods){
            apiResponse('0','商品信息有误');
        }
        //goods.picture, goods.goods_pic, master.head_pic, master.field_id, master.social_id
        $head_pic = $this ->searchPhoto($goods['head_pic']);
        $picture = $this ->searchPhoto($goods['goods_pic']);
        $goods['head_pic'] = $head_pic?$head_pic:C("API_URL").'/Uploads/Master/default.png';
        $goods['goods_pic']  = $picture?$picture:'';
        $goods['price'] = $this ->goodsPrice($goods['price'],1);
        $goods['master_easemob_account']  = $goods['easemob_account'];
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
            $goods['field_info'] = $field_info;
        }else{
            $goods['field_info'] = array();
        }

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

        $goods['picture'] = $pic_info;
        $order_num = $this ->getOrderNum($goods['goods_id']);
        $goods['order_num'] = $order_num?$order_num.'':'0';
        //查看用户是否收藏该商品
        if($member){
            $where = array('goods_id'=>$request['goods_id'],'m_id'=>$member['id'],'status'=>array('neq',9));
            $collect = $this ->easyMysql('Collect','3',$where);
            if($collect){
                $goods['collect'] = '1';
            }else{
                $goods['collect'] = '2';
            }
        }else{
            $goods['collect'] = '2';
        }
        //查看商品评价
        $where = array('comment.goods_id'=>$request['goods_id'],'comment.status'=>array('neq',9));
        $field = 'comment.id as comment_id, comment.evaluate_star, comment.content, comment.create_time, member.head_pic, member.nickname, comment.content_pic';
        $order = 'comment.create_time desc';
        $comment = D('MasterService') ->selectComment($where, $field, $order, 3);
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
        $goods['comment'] = $comment;
        $goods['comment_num'] = $comment_num;

        apiResponse('1','',$goods);
    }

    /**
     * 大师的宝阁
     */
    public function masterGoods($request = array()){
        //大师宝阁信息
        $where = array('goods.status'=>1,'goods.frame'=>1, 'goods.master_id'=>$request['master_id'], 'master.status'=>1);
        $field = 'goods.id as goods_id, goods.goods_name, goods.goods_pic, goods.price, goods.goods_info, master.nickname, master.head_pic, master.auth_status, master.social_id';
        $order = 'goods.sort desc, goods.create_time desc';
        $goods = D('Goods') ->selectGoods($where, $field, $order, '', $request['p']);
        if(!$goods){
            $goods = array();
        }else{
            foreach($goods as $k =>$v){
                unset($picture);
                unset($goods_num);
                //goods.picture, master.head_pic, master.social_id
                $head_pic = $this ->searchPhoto($v['head_pic']);
                $picture = $this ->searchPhoto($v['goods_pic']);
                $goods[$k]['head_pic'] = $head_pic?$head_pic:C("API_URL").'/Uploads/Master/default.png';
                $goods[$k]['goods_pic']  = $picture?$picture:'';
                if($v['social_id'] != 0){
                    $goods[$k]['social_status'] = '1';
                }else{
                    $goods[$k]['social_status'] = '2';
                }
                $goods_num = $this ->getOrderNum($v['goods_id']);
                $goods[$k]['order_num'] = $goods_num?$goods_num.'':'0';
                $goods[$k]['price'] = $this ->goodsPrice($v['price'],1);
            }
        }

        apiResponse('1','',$goods);
    }

    /**
     * 商品评价列表
     */
    public function goodsEvaluate($request = array()){
        $where = array('comment.goods_id'=>$request['goods_id'],'comment.status'=>array('neq',9));
        $field = 'comment.id as comment_id, comment.evaluate_star, comment.content, comment.create_time, member.head_pic, member.nickname, comment.content_pic';
        $order = 'comment.create_time desc';
        $comment = D('MasterService') ->selectComment($where, $field, $order, '', $request['p']);
        if(!$comment){
            $comment = array();
        }else{
            foreach($comment as $k => $v){
                unset($head_pic);
                unset($pic);
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

        apiResponse('1','',$comment);
    }

    /**
     * 加入购物车
     */
    public function addShopCart($request = array()){
        $member = $this ->searchMember($request['token']);
        $where = array('id'=>$request['goods_id'],'status'=>1,'frame'=>1);
        $goods = $this ->easyMysql('Goods','3',$where);

        if(!$goods){
            apiResponse('0','商品详情有误');
        }
        unset($where);
        $where['m_id']     = $member['id'];
        $where['goods_id'] = $goods['id'];
        $where['status']   = array('neq',9);
        $res = $this ->easyMysql('Shopcart',3,$where);
        if($res){
            $result = $this ->setType('Shopcart',$where,'number',$request['number'],1);
        }else{
            $data['m_id']      = $member['id'];
            $data['master_id'] = $goods['master_id'];
            $data['goods_id']  = $goods['id'];
            $data['number']    = $request['number'];
            $data['create_time'] = time();
            $result = $this ->easyMysql('Shopcart', 1, '', $data);
        }

        if(!$result){
            apiResponse('0','加入购物车失败');
        }
        apiResponse('1','加入购物车成功');
    }

    /**
     * 修改购物车
     * 需要传递的参数：
     * 购物车属性  cart_json    json串：[{"cart_id":"4","number":"5"},{"cart_id":"10","number":"6"}]
     */
    public function modifyShopCart($request = array()){
        $member = $this ->searchMember($request['token']);
        //购物车属性值不能为空   并转化json串
        if(empty($_POST['cart_json'])){
            apiResponse('0','购物车属性不能为空');
        }
        $cart_list = json_decode($_POST['cart_json'],true);
        if(empty($cart_list)){
            apiResponse('0','JSON错误');
        }
        //对json 串进行转化并操作
        foreach ($cart_list as $k => $v){
            $cart['cart_id'] = $cart_list[$k]['cart_id'];
            $cart['num'] = $cart_list[$k]['num'];
            if(empty($cart['cart_id'] || empty($cart['num']))){
                continue;
            }
            $where = array('id'=>$v['cart_id'],'m_id'=>$member['id'],'status'=>array('neq',9));
            $res = $this ->easyMysql('Shopcart','3',$where);
            if(!$res){
                continue;
            }
            $data['number'] = $v['number'];
            $data['update_time'] = time();
            $result = $this ->easyMysql('Shopcart','2',$where,$data);
            if(!$result){
                continue;
            }
        }
        apiResponse('1','操作成功');
    }

    /**
     * 删除购物车
     * 购物车ID  cart_id  购物车ID json串：[{"cart_id":"1"},{"cart_id":"2"}]
     */
    public function deleteShopCart($request = array()){
        $member = $this ->searchMember($request['token']);
        //对购物车json串进行操作
        $cart_id_list = json_decode($_POST['cart_json'],true);
        if(empty($cart_id_list)){
            apiResponse('0', 'JSON错误');
        }
        $cart = array();
        foreach ($cart_id_list as $k => $v){
            $cart[] = $v['cart_id'];
        }
        if(empty($cart)){
            apiResponse('0', 'JSON错误');
        }
        //对选中商品进行删除
        $where = array('id'=>array('IN',$cart));
        $data['update_time'] = time();
        $data['status'] = 9;
        $result = $this ->easyMysql('Shopcart','2',$where,$data);
        if(empty($result)){
            apiResponse('0', '删除有误');
        }
        apiResponse('1', '删除成功');
    }

    /**
     * 购物车列表
     */
    public function shopCartList($request = array()){
        $member = $this ->searchMember($request['token']);
        $where  = array('m_id'=>$member['id'],'status' =>array('neq',9));
        $field  = 'id as cart_id , goods_id, number, master_id';
        $cart_list = $this ->easyMysql('Shopcart','4',$where,'',$field);

        if(empty($cart_list)) {
            $result_data['cart_list'] = array();
            apiResponse('1', '', $result_data);
        }
        //购物车有商品的情况 操作步骤：1，拼接属性显示，2分组，3。分组排序 sort函数
        $cart = array();
        //attr_value  属性值
        foreach($cart_list as $k =>$v){
            unset($master);
            //获取商家信息
            $master = $this ->easyMysql('Master','3',array('id'=>$v['master_id'],'status'=>1),'','id as master_id, nickname') ;
            if(!$master){
                continue;
            }
            $cart_list[$k]['master_id'] = $master['master_id']?$master['master_id']:'';
            $cart_list[$k]['master_name'] = $master['nickname']?$master['nickname']:'';
            $where = array('id'=>$v['goods_id'],'frame'=>1,'status'=>1);
            $field = 'id as goods_id, goods_pic, goods_name, price';
            $goods_info = $this ->easyMysql('Goods','3',$where,'',$field);
            if(!$goods_info){
                continue;
            }
            $cart_list[$k]['goods_name'] = $goods_info['goods_name'];

            $goods_pic = $this ->searchPhoto($goods_info['goods_pic']);
            $cart_list[$k]['goods_pic'] = $goods_pic?$goods_pic:'';
            $cart_list[$k]['price'] = $this ->goodsPrice($goods_info['price'],1);
            $cart[] = $cart_list[$k];
        }

        //组装格式
        unset($cart_list);
        foreach($cart as $k =>$v){
            $cart_list[$v['master_id']]['goods'][]     = $v;
            $cart_list[$v['master_id']]['master_id']   = $v['master_id'];
            $cart_list[$v['master_id']]['master_name'] = $v['master_name'];
        }
        sort($cart_list);
        if(!$cart_list){
            $cart_list = array();
        }
        $result_data['cart_list'] = $cart_list;

        apiResponse('1', '', $result_data);
    }
}