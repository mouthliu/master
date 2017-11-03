<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class AddressController
 * @package Api\Controller
 * 收货地址模块
 */
class AddressController extends BaseController{
    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }
    /*
     * 新增收货地址
     * 用户标识    token
     * 收货人姓名  name
     * 联系方式    telephone
     * 收货人地址  address_info
     * 省地址      province_id
     * 市地址      city_id
     * 区地址      area_id
     */
    public function addAddress(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        if(empty($_POST['name'])){
            apiResponse('0','请输入收货人姓名');
        }
        if(empty($_POST['telephone'])){
            apiResponse('0','请输入收货人电话');
        }
        if(empty($_POST['address_info'])){
            apiResponse('0','请输入收货地址');
        }
        if(empty($_POST['province_id'])){
            apiResponse('0','请选择所在省');
        }
        if(empty($_POST['city_id'])){
            apiResponse('0','请选择所在市');
        }
        if(empty($_POST['area_id'])){
            apiResponse('0','请选择所在区');
        }
        D('Address','Logic')->addAddress(I('post.'));
    }
    /*
     * 修改收货地址
     * 用户ID     m_id
     * 收货人姓名 consignee
     * 联系方式   mobile
     * 收货地址   address
     * 默认地址   is_default
     */
    public function modifyAddress(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //地址ID不能为空
        if(empty($_POST['address_id'])){
            apiResponse('0','地址ID不能为空');
        }
        D('Address','Logic')->modifyAddress(I('post.'));
    }
    /*
     * 收货地址列表
     * 用户token    token
     */
    public function addressList(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        D('Address','Logic')->addressList(I('post.'));
    }
    /*
     * 删除收货地址
     * 用户token  token
     * 地址ID     address_id
    */
    public function deleteAddress(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //地址ID不能为空
        if(empty($_POST['address_id'])){
            apiResponse('0','地址ID不能为空');
        }
        D('Address','Logic')->deleteAddress(I('post.'));
    }

    /*
     * 收货地址详情
     * 用户token     token
     * 地址id：      address_id
     * 设置状态      1  设为默认  2  解除默认
     */
    public function addressInfo(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //地址ID不能为空
        if(empty($_POST['address_id'])){
            apiResponse('0','地址ID不能为空');
        }
        D('Address','Logic')->addressInfo(I('post.'));
    }

    /*
     * 设置默认地址
     * 传递参数的方式：post
     * 需要传递的参数：
     * 地址id：address_id
     * 设置状态      1  设为默认  2  解除默认
     */
    public function defaultAddress(){
        if(empty($_SERVER['HTTP_TOKEN']) && empty($_POST['token'])){
            apiResponse('-1','账号已过期，请重新登录');
        }elseif($_SERVER['HTTP_TOKEN']){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //地址ID不能为空
        if(empty($_POST['address_id'])){
            apiResponse('0','地址ID不能为空');
        }
        if($_POST['type'] != 1&&$_POST['type'] != 2){
            apiResponse('0','默认类型有误');
        }
        D('Address','Logic')->defaultAddress(I('post.'));
    }

    /**
     * 全国三级地址库修改版
     * 传递参数的方式：post
     * 需要传递的参数：父级id
     */
    public function cityLibrary(){
        D('Address','Logic') ->cityLibrary(I('post.'));
    }

    /**
     * 城市列表
     */
    public function cityList(){
        D('Address','Logic') ->cityList(I('post.'));
    }
}
