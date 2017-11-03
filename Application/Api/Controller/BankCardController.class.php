<?php
namespace Api\Controller;
use Think\Controller;

/**
 * Class BankCardController
 * @package Api\Controller
 * 银行卡模块
 */
class BankCardController extends BaseController{
    /**
     * 初始化
     */
    public function _initialize(){
    }
    /*
     * 支持的银行卡列表
     */
    public function chooseBank(){
        D('BankCard','Logic') -> chooseBank();
    }
    /*
     * 添加银行卡
     * 用户ID           m_id
     * 银行卡类别       support_bank_id
     * 银行卡号         bank_number
     * 用户姓名         name
     * 银行预留手机号   telephone
     * 用户类型         user_type
     */
    public function addBankCard(){
        if(empty($_SERVER['HTTP_TOKEN'])&&empty($_POST['token'])){
            apiResponse('-1','请重新登陆');
        }elseif(!empty($_SERVER['HTTP_TOKEN'])){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }

        //银行卡ID不能为空
        if(empty($_POST['support_bank_id'])){
            apiResponse('0','请选择银行卡类别');
        }
        //银行卡号不能为空
        if(empty($_POST['bank_number'])){
            apiResponse('0','请填写银行卡号');
        }
        //用户姓名不能为空
        if(empty($_POST['name'])){
            apiResponse('0','请填写用户姓名');
        }
        //银行预留手机号不能为空
        if(empty($_POST['telephone'])){
            apiResponse('0','请填写银行预留手机号');
        }
        //用户类型不能为空
        if($_POST['user_type'] != 1&&$_POST['user_type'] != 2){
            apiResponse('0','用户类型有误');
        }
        D('BankCard','Logic') -> addBankCard(I('post.'));
    }
    /*
     * 银行卡列表
     * 用户token
     * 用户类型  user_type
     */
    public function bankCardList(){
        if(empty($_SERVER['HTTP_TOKEN'])&&empty($_POST['token'])){
            apiResponse('-1','请重新登陆');
        }elseif(!empty($_SERVER['HTTP_TOKEN'])){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //用户类型不能为空
        if($_POST['user_type'] != 1&&$_POST['user_type'] != 2){
            apiResponse('0','用户类型有误');
        }
        D('BankCard','Logic') -> bankCardList(I('post.'));
    }
    /*
     * 删除银行卡
     * 用户token   token
     * 银行卡ID    bank_id
     */
    public function deleteBankCard(){
        if(empty($_SERVER['HTTP_TOKEN'])&&empty($_POST['token'])){
            apiResponse('-1','请重新登陆');
        }elseif(!empty($_SERVER['HTTP_TOKEN'])){
            $_POST['token'] = $_SERVER['HTTP_TOKEN'];
        }
        //银行卡ID不能为空
        if(empty($_POST['bank_id'])){
            apiResponse('0','银行卡ID不能为空');
        }
        //用户类型不能为空
        if($_POST['user_type'] != 1&&$_POST['user_type'] != 2){
            apiResponse('0','用户类型有误');
        }
        D('BankCard','Logic') -> deleteBankCard(I('post.'));
    }
}
