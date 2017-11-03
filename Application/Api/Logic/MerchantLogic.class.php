<?php
namespace Api\Logic;
/**
 * Class MerchantLogic
 * @package Api\Logic
 * 商家模块
 */
class MerchantLogic extends BaseLogic{
    /**
     * 个人验证
     * 用户ID        m_id
     * 认证类型      type  1  企业认证  2  个人认证
     * 负责人姓名    charge_name
     * 联系方式      telephone
     * 公司名称      company_name
     * 四张认证图片  license  frontal  back  handheld
     */
    public function verification($request = array()){
        //用户ID不能为空
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }
        //负责人姓名不能为空
        if(!$request['charge_name']){
            apiResponse('error','负责人姓名不能为空');
        }
        //联系方式不能为空
        if(!$request['telephone']){
            apiResponse('error','联系方式不能为空');
        }
        //认证类型不能有误  type   1  企业认证  2  个人认证
        if($request['type'] != 1&&$request['type'] != 2){
            apiResponse('error','认证类型有误');
        }

        $merchant = M('Merchant') ->where(array('m_id'=>$request['m_id'])) ->find();
        if($merchant){
            apiResponse('error','该用户已注册成为商家');
        }

        //开始认证
        $data['m_id'] = $request['m_id'];
        if($request['type'] == 1){
            if(!$request['company_name']){
                apiResponse('error','公司名称不能为空');
            }
            $where['company_name'] = $request['company_name'];
            $result = M("merchant") ->where($where) ->find();
            if($result){
                apiResponse('error','该公司已被注册');
            }
            $data['type'] = 1;
            $data['company_name'] = $request['company_name'];
            $data['charge_name']  = $request['charge_name'];
            $data['telephone']    = $request['telephone'];

            if($_FILES['license']['name'] || $_FILES['frontal']['name'] || $_FILES['back']['name'] || $_FILES['handheld']['name'] ){
                $res = api('UploadPic/upload', array(array('save_path' => "Merchant")));
                foreach ($res as $k => $value) {
                    if($value['key'] == 'license'){
                        $data['license'] = $value['id'];
                    }
                    if($value['key'] == 'frontal'){
                        $data['frontal'] = $value['id'];
                    }
                    if($value['key'] == 'back'){
                        $data['back'] = $value['id'];
                    }
                    if($value['key'] == 'handheld'){
                        $data['handheld'] = $value['id'];
                    }
                }
            }
        }else{
            $data['type'] = 2;
            $data['charge_name']  = $request['charge_name'];
            $data['telephone']    = $request['telephone'];

            if($_FILES['frontal']['name'] || $_FILES['back']['name'] || $_FILES['handheld']['name']){
                $res = api('UploadPic/upload', array(array('save_path' => "Merchant")));
                foreach ($res as $k => $value) {
                    if($value['key'] == 'frontal'){
                        $data['frontal'] = $value['id'];
                    }
                    if($value['key'] == 'back'){
                        $data['back'] = $value['id'];
                    }
                    if($value['key'] == 'handheld'){
                        $data['handheld'] = $value['id'];
                    }
                }
            }
        }
        $data['create_time'] = time();
        $merchant = M("Merchant") ->add($data);
        if(!$merchant){
            apiResponse('error','提交认证失败');
        }
        //认证之后改变用户状态
        unset($where);
        unset($data);
        $where['id'] = $request['m_id'];
        $data['merchant_status'] = 1;
        $data['update_time'] = time();
        $member = M("Member") ->where($where) ->data($data) ->save();
        if(!$member){
            apiResponse('error','提交认证失败');
        }
        apiResponse('success','提交认证成功');
    }

    /**
     * 查看认证信息
     * 用户ID       m_id
     */
    public function checkVerification($request = array()){
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }
        $where['id'] = $request['m_id'];
        $where['status'] = array('neq',9);
        $member_info = M("Member") ->where($where) ->find();
        if(!$member_info){
            apiResponse('error','该用户状态有误');
        }
        $merchant = M("Merchant") ->where(array('m_id'=>$request['m_id'])) ->getField('type');
        unset($where);
        $where['m_id'] = $request['m_id'];
        if($merchant['type'] == 1){
            $merchant_info = M("Merchant") ->where($where) ->field('id as merchant_id, m_id, company_name, charge_name, telephone, license, frontal, back, handheld') ->find();
            if(!$merchant_info){
                apiResponse('error','商家信息有误');
            }
            $path1 = M("File") -> where(array('id'=>$merchant_info['license'])) ->getField('path');
            $merchant_info['license'] = $path1?C("API_URL").$path1:'';
            $path2 = M("File") -> where(array('id'=>$merchant_info['frontal'])) ->getField('path');
            $merchant_info['frontal'] = $path2?C("API_URL").$path2:'';
            $path3 = M("File") -> where(array('id'=>$merchant_info['back'])) ->getField('path');
            $merchant_info['back'] = $path3?C("API_URL").$path3:'';
            $path4 = M("File") -> where(array('id'=>$merchant_info['handheld'])) ->getField('path');
            $merchant_info['handheld'] = $path4?C("API_URL").$path4:'';
        }else{
            $merchant_info = M("Merchant") ->where($where) ->field('id as merchant_id, m_id, charge_name, telephone, frontal, back, handheld') ->find();
            if(!$merchant_info){
                apiResponse('error','商家信息有误');
            }
            $path1 = M("File") -> where(array('id'=>$merchant_info['frontal'])) ->getField('path');
            $merchant_info['frontal'] = $path1?C("API_URL").$path1:'';
            $path2 = M("File") -> where(array('id'=>$merchant_info['back'])) ->getField('path');
            $merchant_info['back'] = $path2?C("API_URL").$path2:'';
            $path3 = M("File") -> where(array('id'=>$merchant_info['handheld'])) ->getField('path');
            $merchant_info['handheld'] = $path3?C("API_URL").$path3:'';
        }
        apiResponse('success','获取成功',$merchant_info);
    }

    /**
     * 商家和个人微商分类
     */
    public function merchantType($request = array()){
        //直接查询分类单
        $merchant_type = M("MerchantType") ->where(array('type'=>1,'is_app_show'=>1,'status'=>array('neq',9)))
            ->field('id as m_t_id, type_name, logo') ->order('create_time desc,id desc') ->limit(8) ->select();
        if(!$merchant_type){
            $merchant = array();
            apiResponse('success','目前没有分类信息',$merchant_type);
        }
        //获取分类并查询图像
        $index = 0;
        foreach($merchant_type as $k =>$v){
            $path = M('File') ->where(array('id'=>$v['logo'])) ->getField('path');
            $v['logo'] = $path?C("API_URL").$path:'';
            $merchant[$index]['m_t_id'] = $v['m_t_id'];
            $merchant[$index]['type_name'] = $v['type_name'];
            $merchant[$index]['logo'] = $v['logo'];
            $index += 1;
        }
        if(!$merchant){
            $merchant = array();
        }
        apiResponse('success','获取成功',$merchant);
    }

    /**
     * 商家列表
     * 参数：
     * 经度：lng
     * 纬度：lat
     * 商家分类ID(如果为空，表示全部分类)：merchant_type_id
     * sort_order:0综合排序，1销量最高，2速度最快，3距离最近，4评分最高，5起送价最低
     * delivery:是否是天马配送。1是天马配送 2不是天马配送
     * active:1优惠商家，2首单优惠，3满减优惠
     * 分页参数：p
     * 商家分类  type  1  企业认证（正常商家）  2  个人认证（个人微商）
     */
    public function merchantList($request = array()){
        if(!$request['region_name']){
            apiResponse('error','市级地址不能为空');
        }
        //是否是同城商家
        $where['region_name'] = array('like','%'.$request['region_name'].'%');
        $where['region_type'] = 2;
        $region_id = M('Region') ->where($where) ->find();
        unset($where);
//        $where['city_id'] = $region_id['id'];

        //经纬度不能为空
        if(empty($request['lng']) && empty($request['lat'])){
            apiResponse('error','定位失败');
        }
        //搜索字可以为空
        if($request['keywords']){
            $merchant_name = $request['keywords'];
        }
        //商家类型不能为空
        if(!$request['type']){
            apiResponse('error','请选择商家类型');
        }
        //查询相应商品
        if($merchant_name){
            $where['goods_name'] = array('like','%'.$merchant_name.'%');
            $goods = M('Goods') ->where($where) ->field('merchant_id') ->select();
            $merchant_id = array();
            foreach($goods as $k =>$v){
                $merchant_id[] = $v['merchant_id'];
            }
            $merchant_id = array_unique($merchant_id);
        }
        unset($where);

        //sort_order:0综合排序，1销量最高，2速度最快，3距离最近，4评分最高，5起送价最低
        if($request['sort_order']!=0 && $request['sort_order']!=1 && $request['sort_order']!=2 && $request['sort_order']!=3 && $request['sort_order']!=4 && $request['sort_order']!=5){
            apiResponse('error','排序参数错误');
        }



        //是否是天马配送   1  是  2  不是
        if($request['delivery']){
            if($request['delivery'] != 1 && $request['delivery'] != 2){
                apiResponse('error','delivery参数错误');
            }
            $where['delivery'] = $request['delivery'];
        }

        //活动类别  1  优惠商家  2  首单立减  3  满减优惠
        if($request['active']){
            if($request['active']!=1 && $request['active']!=2 && $request['active']!=3){
                apiResponse('error','active参数错误');
            }
        }

        //分页参数不能为空
        if(empty($request['p'])){
            apiResponse('error','分页参数不能为空');
        }

        $result_data = array();//收集需要返回的字段
        //获取商家分类
        $merchant_type_list = M('MerchantType')->where(array('type'=>1 ,'is_app_show'=>1 ,'status'=>array('eq',1)))
            ->field('id as merchant_type_id,type_name') ->order('create_time desc') ->limit(7) ->select();
        $result_data['merchant_type_list'] = $merchant_type_list?$merchant_type_list:array();

        //获取商家信息
        if($request['merchant_type_id']){
            if($request['merchant_type_id'] == 7){
                $where['city_id'] = $region_id['id'];
            }else{
                $where['merchant_type'] = $request['merchant_type_id'];
            }
        }

        $where['city_id'] = $region_id['id'];
        //组装优惠活动条件
        if($request['active']){
            $w['status'] = array('eq',1);
            if($request['active']!=1){
                $w['type']   = $request['active']==2?2:1;
            }
            $merchant_id_list = M('Active')->where($w)->field('merchant_id')->group('merchant_id')->select();
            if($merchant_id_list){
                foreach($merchant_id_list as $k =>$v){
                    $mer_id_arr[] = $v['merchant_id'];
                }
                $where['id'] = array('in',$mer_id_arr);
            }
        }

        //组装排序条件
        switch($request['sort_order']){
            case 0:$sort = 'sales DESC';break;
            case 1:$sort = 'sales DESC';break;
            case 2:$sort = 'delivery_time DESC';break;
            case 3:$sort = 'distance ASC';break;
            case 4:$sort = 'score DESC';break;
            case 5:$sort = 'lowst_price ASC';break;
            default:$sort = 'create_time DESC';
        }

        if($merchant_name){
            $map['merchant_name'] = array('like','%'.$merchant_name.'%');
            if($merchant_id){
                $map['id']     = array('IN', $merchant_id);
            }
            $map['_logic'] = 'OR';
            $where['_complex'] = $map;
        }
        $where['type'] = $request['type'];
        $earthRadius = 6367000;
        $lng = $request['lng']?$request['lng']:117.159644;
        $lat = $request['lat']?$request['lat']:39.098411;
        $where['distance'] = array('lt',10000000);

        $where['status']   = array('neq',9);
        $merchant_list = M('Merchant')->where($where)
            ->field("id AS merchant_id,merchant_type,merchant_name,head_pic,lowst_price,delivery_price,score,delivery_time,merchant_status,delivery,deposit,sales,ROUND($earthRadius*2*asin(sqrt(pow(sin((lat*PI()/180-$lat*PI()/180)/2),2)+cos($lat*PI()/180)*cos(lat*PI()/180)*pow(sin((lng*PI()/180-$lng*PI()/180)/2),2)))) as distance")
            ->order($sort)
            ->page($request['p'].',10')
            ->select();
        $merchant_list = $merchant_list?$merchant_list:array();

        if(!$merchant_list){
            $merchant_list = array();
        }

        foreach($merchant_list as $k =>$v){
            $path = M('File') ->where(array('id'=>$v['head_pic'])) ->getField('path');
            $merchant_list[$k]['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Merchant/default.png';
            $merchant_type = M('MerchantType') ->where(array('id'=>$v['merchant_type'],'type'=>1,'status'=>array('neq',9))) ->getField('type_name');
            $merchant_list[$k]['merchant_type'] = $merchant_type?$merchant_type:'';
            unset($where);
            $where['merchant_id'] = $v['merchant_id'];
            $where['status'] = array('neq',9);
            $active = M('Active') ->where($where) ->field('id as active_id, type, condition, price') ->order('type asc') ->select();
            if(!$active){
                $active = array();
            }
            $merchant_list[$k]['active'] = $active;
        }
        $result_data['merchant_list'] = $merchant_list;
        apiResponse('success','获取商家列表成功',$result_data);
    }

    /**
     * 商家列表和个人微商商品页
     * 商家名称      merchant_id
     */
    public function goodsList($request = array()){
        //商家ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        //查询商家信息
        $where['id'] = $request['merchant_id'];
        $where['status'] = 1;
        $merchant = M("Merchant") ->where($where) ->field('id as merchant_id, head_pic, merchant_name, lowst_price, delivery_price, delivery_time') ->find();
        if(!$merchant){
            apiResponse('error','商家信息有误');
        }
        $path = M("File") ->where(array('id'=>$merchant['head_pic'])) ->getField('path');
        $merchant['head_pic'] = $path?C("API_URL").$path:'';
        unset($where);
        //查询该商家旗下的类名
        $where['merchant_id'] = $request['merchant_id'];
        $where['status']      = 1;
        $where['type']        = 1;
        $merchant['goods_type'] = M("GoodsType") ->where($where) ->field('id as g_t_id, type_name') ->select();
        if(!$merchant['goods_type']){
            $merchant['goods_type'] = array();
        }
        //查询该商家类名下的商品名
        $index = 0;
        foreach($merchant['goods_type'] as $k =>$v){
            unset($where);
            $where['merchant_id'] = $request['merchant_id'];
            $where['status']      = 1;
            $where['g_t_id'] = $v['g_t_id'];
            $goods = M("Goods") ->where($where) ->field('id as goods_id ,goods_name ,goods_pic ,sales ,goods_price')->select();
            foreach($goods as $key =>$val){
                $path = M("File") ->where(array('id'=>$val['goods_pic'])) ->getField('path');
                $goods[$key]['goods_pic'] = $path?C("API_URL").$path:'';
            }
            if(!$goods){
                $goods = array();
            }
            $merchant['goods_type'][$k]['goods'] = $goods;
            $index += 1;
        }
        $merchant['goods_type'] = array_values($merchant['goods_type']);
        apiResponse('success','操作成功',$merchant);
    }

    /**
     * 商家列表和个人微商评论页
     * 商家ID       merchant_id
     * 分页参数     p
     */
    public function merchantEvaluate($request = array()){
        //商家ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        //分页参数不能为空
        if(!$request['p']){
            apiResponse('error','分页参数不能为空');
        }
        //查询商家信息
        $where['id'] = $request['merchant_id'];
        $merchant_info = M("Merchant") ->where($where) ->field('id as merchant_id, merchant_name, head_pic, lowst_price, delivery_price, score, delivery_time')->find();
        if(!$merchant_info){
            apiResponse('error','商家信息有误');
        }
        $path = M("File") ->where(array('id'=>$merchant_info['head_pic'])) ->getField('path');
        $merchant_info['head_pic'] = $path?C("API_URL").$path:'';
        //根据商家信息查询评论并分页
        unset($where);
        $where['merchant_id'] = $request['merchant_id'];
        $count = M("GoodsComment") ->where($where) ->count();
//        $delivery_time = M('GoodsComment') -> where($where) ->getField('SUM(delivery_time) as delivery_time');
//        $merchant_info['delivery_time'] = ceil(($delivery_time+40)/($count+1));
        $comment = M("GoodsComment") ->where($where) -> field('id as com_id, order_id, m_id, score, content, delivery_time, create_time')
            ->order('create_time desc') ->page($request['p'].',10') ->select();
        if(!$comment){
            $merchant_info['comment'] = array();
            apiResponse('success','获取商家评论成功',$merchant_info);
        }
        //查询评论者的资料
        foreach($comment as $k =>$v){
            unset($where);
            $where['id'] = $v['m_id'];
            $comment[$k]['member'] = M('Member') ->where($where) ->field('id as m_id, nickname, head_pic') ->find();
            $path = M('File') ->where(array('id'=>$comment[$k]['member']['head_pic'])) ->getField('path');
            $comment[$k]['member']['head_pic'] = $path?C("API_URL").$path:C('API_URL').'/Uploads/Member/default.png';
            if(!$comment[$k]['member']){
                $comment[$k]['member']['nickname'] = '同城网用户';
                $comment[$k]['member']['head_pic'] = C('API_URL').'/Uploads/Member/default.png';
            }
            $order = M('Order') ->where(array('id'=>$v['order_id'])) ->find();
            $goods_info = unserialize($order['goods_info']);
            $goods = '';
            foreach($goods_info as $key =>$val){
                $goods = $val['goods_name'].' ,'.$goods;
            }
            $comment[$k]['goods_data'] = substr($goods, 0, -1);
        }
        $merchant_info['comment'] = $comment;
        apiResponse('success','操作成功',$merchant_info);
    }

    /**
     * 商家和个人微商详情页
     * 商家ID    merchant_id
     */
    public function merchantInfo($request = array()){
        //商家ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        if($request['m_id']){
            $where['m_id']    = $request['m_id'];
            $where['merchant_id'] = $request['merchant_id'];
            $collect = M("MerchantCollect") ->where($where) ->find();
            if($collect){
                $collect = 1;
            }else{
                $collect = 0;
            }
        }else{
            $collect = 0;
        }
        //查询商家详情
        unset($where);
        $where['id'] = $request['merchant_id'];
        $where['status'] = 1;
        $merchant_info = M("Merchant") ->where($where)
            ->field('id as merchant_id, merchant_name, head_pic, lowst_price, delivery_price, merchant_address, start_time, end_time, score, delivery_time, sales, hotline')
            ->find();
        if(!$merchant_info){
            apiResponse('error','商家信息有误');
        }
        $path = M("File") ->where(array('id'=>$merchant_info['head_pic'])) ->getField('path');
        $merchant_info['head_pic'] = $path?C("API_URL").$path:'';
        $count = M('GoodsComment') -> where(array('merchant_id'=>$request['merchant_id'])) ->count();
        $merchant_info['collect'] = ''.$collect;
        apiResponse('success','成功获取商家信息',$merchant_info);
    }

    /**
     * 商品详情页
     * 商品ID    goods_id
     */
    public function goodsInfo($request = array()){
        //商品ID不能为空
        if(!$request['goods_id']){
            apiResponse('error','商品ID不能为空');
        }
        //查询商品属性
        $where['id'] = $request['goods_id'];
        $where['status'] = 1;
        $goods = M('Goods') ->where($where) ->field('id as goods_id, goods_name, goods_pic, goods_price, sales') ->find();
        if(!$goods){
            apiResponse('error','商品详情信息有误');
        }
        $path = M("File") ->where(array("id"=>$goods['goods_pic']))->getField('path');
        $goods['goods_pic'] = $path?C("API_URL").$path:'';
        apiResponse('success','成功获取商品详情',$goods);
    }

    /**
     * 收藏与取消收藏商家
     * 商家ID     merchant_id
     * 用户ID     m_id
     * 类型       type  1  收藏  2  取消收藏
     */
    public function merchantCollect($request = array()){
        //商家ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        //用户ID不能为空
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }
        //操作类型type  1  收藏  2  取消收藏
        if($request['type'] != 1&&$request['type'] != 2){
            apiResponse('error','操作类型有误');
        }
        if($request['type'] == 1){
            $data['m_id'] = $request['m_id'];
            $data['merchant_id'] = $request['merchant_id'];
            $data['create_time'] = time();
            $result = M('MerchantCollect') ->add($data);
            if(!$result){
                apiResponse('error','收藏失败');
            }
            apiResponse('success','收藏成功');
        }else{
            $where['m_id'] = $request['m_id'];
            $where['merchant_id'] = $request['merchant_id'];
            $result = M("MerchantCollect") ->where($where) ->delete();
            if(!$result){
                apiResponse('error','取消收藏失败');
            }
            apiResponse('success','取消收藏成功');
        }
    }

    /**
     * 评论接口
     * 用户ID     m_id
     * 商家ID     merchant_id
     * 商家评分   score
     * 送餐时间   delivery_time
     * 评论内容   content
     */
    public function comment($request = array()){
        //用户ID不能为空
        if(!$request['m_id']){
            apiResponse('error','用户ID不能为空');
        }
        //商家ID不能为空
        if(!$request['merchant_id']){
            apiResponse('error','商家ID不能为空');
        }
        //商家评分不能为空
        if(!$request['score']){
            apiResponse('error','商家评分不能为空');
        }
        //送餐时间不能为空
        if(!$request['delivery_time']){
            apiResponse('error','送餐时间不能为空');
        }
        //评论内容可以为空
        if($request['content']){
            $data['content'] = $request['content'];
        }

        if(!$request['order_id']){
            apiResponse('error','订单ID不能为空');
        }
        $where['id'] = $request['order_id'];
        $where['status'] = array('neq',9);
        $order = M('Order') ->where($where) ->find();
        if(!$order){
            apiResponse('error','订单信息有误');
        }
        //新增评论信息
        $data['order_id']      = $request['order_id'];
        $data['m_id']          = $request['m_id'];
        $data['merchant_id']   = $request['merchant_id'];
        $data['score']         = $request['score'];
        $data['delivery_time'] = $request['delivery_time'];
        $data['create_time']   = time();
        $result = M("GoodsComment") ->add($data);
        if(!$result){
            apiResponse('error','评论失败');
        }
        $count = M("GoodsComment") ->where(array('merchant_id'=>$request['merchant_id'])) ->count();
        $score_num = M('GoodsComment') ->where(array('merchant_id'=>$request['merchant_id'])) ->getField('SUM(score) as score');
        $delivery_time_num = M('GoodsComment') ->where(array('merchant_id'=>$request['merchant_id'])) ->getField('SUM(delivery_time) as delivery_time');
        $score = ceil(($score_num + 5)/($count + 1));
        $delivery_time = ceil(($delivery_time_num + 40)/($count + 1));
        unset($where);
        unset($data);
        $where['id'] = $request['merchant_id'];
        $data['score'] = $score;
        $data['delivery_time'] = $delivery_time;
        $data['update_time'] = time();
        $res = M("Merchant") ->where($where) ->data($data) ->save();
        unset($where);
        unset($data);
        $where['id'] = $request['order_id'];
        $data['order_status'] = 6;
        $data['update_time'] = time();
        $data['complete_time'] = time();
        $result_data = M('Order') ->where($where) ->data($data) ->save();
        apiResponse('success','评论成功');
    }
}