<?php
namespace Api\Logic;
/**
 * Class BankCardLogic
 * @package Api\Logic
 * 银行卡模块
 */
class BankCardLogic extends BaseLogic{
    /*
     * 选择银行
     */
    public function chooseBank(){
        //直接查询数据库
        $bank = M('SupportBank') ->field('id as support_bank_id, bank_name, bank_icon') ->select();
        if(empty($bank)){
            $bank = array();
        }
        //查找银行卡图片
        foreach($bank as $k =>$v){
            $bank[$k]['bank_icon'] = C('API_URL').'/Uploads/BankIcon/'.$v['bank_icon'];
        }
        apiResponse('1','',$bank);
    }

    /*
     * 添加银行卡
     * 用户ID           m_id
     * 银行卡类别       support_bank_id
     * 银行卡号         bank_number
     * 用户姓名         name
     * 身份证密码       idcard
     * 银行预留手机号   telephone
     */
    public function addBankCard($request = array()){
        //判断用户账号是否过期
        if($request['user_type'] == 1){
            $member = $this ->searchMember($request['token']);
        }else{
            $member = $this ->searchMaster($request['token']);
        }

        unset($where);
        $where['bank_number'] = $request['bank_number'];
        $where['user_type']   = $request['user_type'];
        $where['status']      = array('neq',9);
        $bank_info   = M("Bank") ->where($where) ->find();
        if($bank_info){
            apiResponse('0','该银行卡已被绑定');
        }

        //添加银行卡账号
        $data['user_id']         = $member['id'];
        $data['user_type']       = $request['user_type'];
        $data['bank_type']       = $request['support_bank_id'];
        $data['bank_number']     = $request['bank_number'];
        $data['name']            = $request['name'];
        $data['telephone']       = $request['telephone'];
        $data['create_time']     = time();
        $data['status']          = 1;
        $result = M('Bank') ->add($data);
        if(!$result){
            apiResponse('0','银行卡添加失败');
        }
        apiResponse('1','银行卡添加成功');
    }
    /*
     * 银行卡列表
     */
    public function bankCardList($request = array()){
        if($request['user_type'] == 1){
            $member = $this ->searchMember($request['token']);
        }else{
            $member = $this ->searchMaster($request['token']);
        }


        //查询用户的所有已添加的银行卡
        $where['bank.user_id']     = $member['id'];
        $where['bank.user_type']   = $request['user_type'];
        $where['bank.status'] = array('neq',9);
        $field = 'bank.id as bank_id, bank.bank_number, support_bank.bank_name';
        $order = 'create_time desc';

        $bank_list = D('Bank') ->selectBank($where, $field, $order);
        if(!$bank_list){
            $bank_list = array();
        }

        apiResponse('1','',$bank_list);
    }
    /*
     * 删除银行卡
     * 绑定银行卡ID    bank_id
     */
    public function deleteBankCard($request = array()){
        if($request['user_type'] == 1){
            $member = $this ->searchMember($request['token']);
        }else{
            $member = $this ->searchMaster($request['token']);
        }

        $where['id'] = $request['bank_id'];
        $where['user_id'] = $member['id'];
        $where['user_type'] = $request['user_type'];
        $where['status'] = array('neq',9);
        $bank_info = $this ->easyMysql('Bank','3',$where);
        if(!$bank_info){
            apiResponse('0','银行卡选择有误');
        }
        $data['status'] = 9;
        $data['update_time'] = time();
        $bank_data = $this ->easyMysql('Bank','2',$where,$data);
        if(empty($bank_data)){
            apiResponse('0','删除失败');
        }
        apiResponse('1','删除成功');
    }
}