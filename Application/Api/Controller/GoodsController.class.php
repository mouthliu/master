<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class MemberController
 * @package Api\Controller
 * 商品管理模块
 */
class GoodsController extends BaseController{

    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }

    /*
    * 商品中心
    * 大师至高无上的标识   token  611bca5bef0ed599518799c1cebe4a4c
     * 分页参数            p
    */
    public function  goodsCenter(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        D('Goods','Logic') ->goodsCenter(I('post.'));
    }
    /*
    * 新增商品
     * 大师至高无上的标识   token    611bca5bef0ed599518799c1cebe4a4c
     * 商品名称             goods_name
     * 上下架状态           frame  1  上架  2  下架
     * 商品类别             g_t_id
     * 商品描述             goods_info
     * 商品运费             freight
     * 商品图片             picture
     * 详情图片——多图上传 goods_pic
     * 商品价格             price
    */
    public function  addGoods(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['goods_name'])){
            apiResponse('0','商品名称不能为空');
        }
        if($_POST['frame'] != 1&&$_POST['frame'] != 2){
            apiResponse('0','上下架状态不能为空');
        }
//        if(empty($_POST['parent_id'])){
//            apiResponse('0','商品一级类别不能为空');
//        }
//        if(empty($_POST['g_t_id'])){
//            apiResponse('0','商品类别不能为空');
//        }
        if(empty($_POST['goods_info'])){
            apiResponse('0','商品描述不能为空');
        }
        if(empty($_POST['price'])){
            apiResponse('0','商品价格不能为空');
        }
        D('Goods','Logic') ->addGoods(I('post.'));
    }

    /*
    * 修改上下架状态
    * 大师至高无上的标识   token    611bca5bef0ed599518799c1cebe4a4c
     * 商品ID              goods_id
     * 操作状态            type  1  上架  2  下架
    */
    public function  goodsFrame(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['goods_id'])){
            apiResponse('0','商品ID不能为空');
        }
        if($_POST['type'] != 1&&$_POST['type'] != 2){
            apiResponse('0','操作状态有误');
        }
        D('Goods','Logic') ->goodsFrame(I('post.'));
    }

    /*
    * 商品分类
    * 父级类别ID  parent_id  可以为空
    */
    public function  goodsType(){
        D('Goods','Logic') ->goodsType(I('post.'));
    }

    /*
    * 修改商品
    * 大师至高无上的标识   token    611bca5bef0ed599518799c1cebe4a4c
     * 商品ID              goods_id
    */
    public function  goodsModify(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['goods_id'])){
            apiResponse('0','商品ID不能为空');
        }
        D('Goods','Logic') ->goodsModify(I('post.'));
    }

    /**
     * 删除商品
     * 大师至高无上的标识   token    611bca5bef0ed599518799c1cebe4a4c
     * 商品ID              goods_id
     */
    public function deleteGoods(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['goods_id'])){
            apiResponse('0','商品ID不能为空');
        }
        D('Goods','Logic') ->deleteGoods(I('post.'));
    }

    /**
     * 商品详情
     * 商品id     goods_id
     */
    public function goodsInfo(){
        if(empty($_POST['goods_id'])){
            apiResponse('0','商品ID不能为空');
        }
        D('Goods','Logic') ->goodsInfo(I('post.'));
    }
}
