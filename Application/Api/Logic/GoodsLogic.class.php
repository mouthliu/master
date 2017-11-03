<?php
namespace Api\Logic;
/**
 * Class MemberLogic
 * @package Api\Logic
 * 大师模块
 */
class GoodsLogic extends BaseLogic{

    /*
    * 商品中心
    * 用户ID   账号token
    */
    public function  goodsCenter($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where['master_id'] = $master['id'];
        $where['status'] = array('neq',9);
        $field = 'id as goods_id, price, goods_pic, frame, goods_name';
        $order = 'frame asc, create_time desc';
        $goods_list = $this ->easyMysql('Goods', '4', $where, '', $field, $order, $request['p']);
        if(!$goods_list){
            $goods_list = array();
        }else{
            unset($where);
            foreach($goods_list as $k => $v){
                unset($picture);
                unset($order_num);
                $order_num = $this ->getOrderNum($v['goods_id']);
                $goods_list[$k]['order_num'] = $order_num?$order_num.'':'0';
                $picture = $this ->searchPhoto($v['goods_pic']);
                $goods_list[$k]['goods_pic'] = $picture?$picture:'';
            }
        }

        apiResponse('1','',$goods_list);
    }

    /*
    * 新增商品
    * 用户ID   账号token
    */
    public function  addGoods($request = array()){
        $master = $this ->searchMaster($request['token']);
        if(!$_POST['goods_type_json']){
            apiResponse('0','商品类别JSON有误');
        }
        $goods_type = json_decode($_POST['goods_type_json'], true);
        if(!$goods_type){
            apiResponse('0','JSON格式有误');
        }
        $first_type = array();
        $type_name  = array();
        foreach($goods_type as $k =>$v){
            $first_type[] = $v['parent_id'];
            $type_name[]  = $v['type_id'];
        }

        $data['master_id'] = $master['id'];
        $data['price']     = $request['price'];
        $data['goods_name'] = $request['goods_name'];
        $data['frame']     = $request['frame'];
        $data['first_type'] = !empty($first_type)?implode(',',$first_type):array();
        $data['goods_type'] = !empty($type_name)?implode(',',$type_name):array();
        $data['goods_info'] = $request['goods_info'];
        $data['freight']   = $request['freight']?$request['freight']:'0.00';
        $data['price']     = $request['price'];
        //发布图片不能为空w_r_pic0 w_r_pic1 w_r_pic2
        if($_FILES['picture']['name'] || $_FILES['goods_pic']['name']){
            $res_pic = api('UploadPic/upload', array(array('save_path' => "Goods")));
            $picture = array();
            foreach ($res_pic as $k => $value) {
                if($value['key'] == 'picture'){
                    $picture[] = $value['id'];
                }
                if($value['key'] == 'goods_pic'){
                    $data['goods_pic'] = $value['id'];
                }
            }
            $data['picture'] = implode(',',$picture);
        }
        $data['create_time'] = time();
        $data['status']      = 1;
        $result = $this ->easyMysql('Goods','1','',$data);

        if(!$result){
            apiResponse('0','添加商品失败');
        }
        apiResponse('1','添加商品成功');
    }

    /*
    * 商品上下架
    * 用户ID   账号token
    */
    public function  goodsFrame($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where['id'] = $request['goods_id'];
        $where['master_id'] = $master['id'];
        $where['status'] = array('neq',9);
        $goods = $this ->easyMysql('Goods','3',$where);
        if(!$goods){
            apiResponse('0','商品信息有误');
        }
        $data['frame'] = $request['type'];
        $data['update_time'] = time();
        $res = $this ->easyMysql('Goods','2',$where,$data);
        if(!$res){
            apiResponse('0','修改状态失败');
        }
        apiResponse('1','操作成功');
    }

    /*
     * 商品分类
     * 父级类别ID  parent_id  可以为空
    */
    public function  goodsType($request = array()){
        $where['parent_id'] = 0;
        $where['status'] = array('neq',9);
        $field = 'id as parent_id,type_name';
        $order = 'sort desc, create_time desc';
        $parent = $this ->easyMysql('GoodsType','4',$where,'',$field,$order);

        if(!$parent){
            $result['parent'] = array();
            $result['goods_type'] = array();
            apiResponse('1','',$result);
        }

        if($request['parent_id']){
            $where['parent_id'] = $request['parent_id'];
        }else{
            $where['parent_id'] = $parent[0]['parent_id'];
        }
        $field = 'id as g_t_id, parent_id, type_name';
        $goods_type = $this ->easyMysql('GoodsType', '4', $where, '',$field, $order);
        if(!$goods_type){
            $goods_type = array();
        }
        $result['parent'] = $parent;
        $result['goods_type'] = $goods_type;

        apiResponse('1','',$result);
    }

    /*
    * 修改商品
    * 大师至高无上的标识   token    611bca5bef0ed599518799c1cebe4a4c
     * 商品ID              goods_id
     * 商品价格            price
     * 商品名称            goods_name
     * 商品上下架          frame
     * 商品类别            g_t_id
     * 商品详情            goods_info
     * 商品运费            freight
     * 商品图片            picture
     * 商品详情图          goods_pic
    */
    public function  goodsModify($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where['id'] = $request['goods_id'];
        $where['master_id'] = $master['id'];
        $where['status'] = array('neq',9);
        $res = $this ->easyMysql('Goods','3',$where);
        if(!$res){
            apiResponse('0','商品信息有误');
        }
        if(!$_POST['goods_type_json']){
            apiResponse('0','商品类别JSON有误');
        }
        $goods_type = json_decode($_POST['goods_type_json'], true);
        if(!$goods_type){
            apiResponse('0','JSON格式有误');
        }
        $first_type = array();
        $type_name  = array();
        foreach($goods_type as $k =>$v){
            $first_type[] = $v['parent_id'];
            $type_name[]  = $v['type_id'];
        }

        $data['first_type'] = !empty($first_type)?implode(',',$first_type):array();
        $data['goods_type'] = !empty($type_name)?implode(',',$type_name):array();

        if($request['price']){
            $data['price']     = $request['price'];
        }
        if($request['goods_name']){
            $data['goods_name'] = $request['goods_name'];
        }
        if($request['frame']){
            $data['frame']     = $request['frame'];
        }
        if($request['parent_id']){
            $data['first_type'] = $request['parent_id'];
        }
        if($request['g_t_id']){
            $data['goods_type'] = $request['g_t_id'];
        }
        if($request['goods_info']){
            $data['goods_info'] = $request['goods_info'];
        }
        //发布图片不能为空w_r_pic0 w_r_pic1 w_r_pic2
        if($_FILES['picture']['name'] || $_FILES['goods_pic']['name']){
            $res_pic = api('UploadPic/upload', array(array('save_path' => "Goods")));
            $picture = array();
            foreach ($res_pic as $k => $value) {
                if($value['key'] == 'picture'){
                    $picture[] = $value['id'];
                }
                if($value['key'] == 'goods_pic'){
                    $data['goods_pic'] = $value['id'];
                }
//                $data['picture'] = implode(',',$picture);
            }
        }

        if($request['picture_old']&&!empty($picture)){
            $data['picture'] = $request['picture_old'].','.implode(',',$picture);
        }elseif(empty($request['picture_old'])&&!empty($picture)){
            $data['picture'] = implode(',',$picture);
        }else{

        }
        $data['freight']   = $request['freight']?$request['freight']:'0.00';
        $data['update_time'] = time();
        $result = $this ->easyMysql('Goods','2',$where,$data);
        if(!$result){
            apiResponse('0','修改商品参数失败');
        }
        apiResponse('1','修改成功');
    }

    /**
     * 删除商品
     */
    public function deleteGoods($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where['id'] = $request['goods_id'];
        $where['master_id'] = $master['id'];
        $res = $this ->easyMysql('Goods','3',$where);
        if(!$res){
            apiResponse('0','商品信息有误');
        }
        $data['status'] = 9;
        $data['update_time'] = time();
        $result = $this ->easyMysql('Goods','2',$where,$data);
        if(!$result){
            apiResponse('0','删除失败');
        }
        apiResponse('1','删除成功');
    }

    /**
     * 商品详情
     */
    public function goodsInfo($request = array()){
        $where  = array('goods.id'=>$request['goods_id'],'goods.status'=>array('neq',9));
        $field  = 'goods.id as goods_id, goods.price, goods.goods_name, goods.goods_pic, goods.picture, goods.frame, goods.goods_info, goods.freight, goods.goods_type';
        $goods  = D('Goods') ->findGoods($where,$field);
        if(!$goods){
            apiResponse('0','商品信息有误');
        }
        $goods_type = explode(',',$goods['goods_type']);
        $type_name  = array();
        foreach($goods_type as $k => $v){
            unset($type);
            $where = array('id'=>$v,'status'=>array('neq',9));
            $field = 'id as type_id, parent_id, type_name';
            $type = $this ->easyMysql('GoodsType',3,$where,'',$field);
            if(!$type){
                continue;
            }
            $type_name[] = $type;
        }
        $goods['type_name_arr'] = !empty($type_name)?$type_name:'';
        $goods_pic = $this ->searchPhoto($goods['goods_pic']);
        $goods['goods_pic'] = $goods_pic?$goods_pic:'';
        if($goods['picture'] != ''){
            $picture = explode(',',$goods['picture']);
            $photo = array();
            foreach($picture as $k => $v){
                unset($pic);
                $pic = $this ->searchPhoto($v);
                $photo[$k]['pic'] = $pic?$pic:'';
                $photo[$k]['picture_id'] = $v;
            }
        }else{
            $photo = array();
        }
        $goods['picture'] = $photo;

        apiResponse('1','',$goods);
    }
}