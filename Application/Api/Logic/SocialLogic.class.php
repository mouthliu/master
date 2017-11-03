<?php
namespace Api\Logic;
/**
 * 协会模块
 */
class SocialLogic extends BaseLogic{
    /*
    * 协会列表
    * 用户ID   账号token
    */
    public function socialList($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where['status'] = 1;
        $field = 'id as social_id, social_name, social_head_pic, social_info';
        $order = 'create_time desc';
        $res = $this ->easyMysql('Social','4',$where,'',$field, $order, $request['p']);
        if(!$res){
            $res = array();
        }
        unset($where);
        foreach($res as $k => $v){
            unset($count);
            $where['apply_status'] = 1;
            $where['status'] = array('neq',9);
            $where['social_id'] = $v['social_id'];
            $count = $this ->easyMysql('SocialApply','6',$where);
            $where['master_id'] = $master['id'];
            unset($where['apply_status']);
            $master_status = $this ->easyMysql('SocialApply','3',$where,'','type ,apply_status');
            $res[$k]['master_status'] = $master_status?$master_status['apply_status'].'':'5';
            $res[$k]['type'] = $master_status?$master_status['type'].'':'2';
            $res[$k]['count'] = $count?$count.'':'0';
        }

        apiResponse('1','',$res);
    }

    /*
    * 申请加入协会
    * 用户ID   账号token
     * zhousl改过,操作数据Social改为SocialApply,应该是增加申请记录,不是修改协会。
    */
    public function application($request = array()){
        $master = $this ->searchMaster($request['token']);
        $data['social_id'] = $request['social_id'];
        $data['master_id'] = $master['id'];
        $data['position']  = 3;
        $data['type']      = 2;
        $data['apply_status'] = 0;
        $data['create_time'] = time();
        $result = $this ->easyMysql('SocialApply','1','',$data);
        if(!$result){
            apiResponse('0','申请失败');
        }
        apiResponse('1','申请成功');
    }

    /*
    * 申请记录
    * 用户ID   账号token
    */
    public function record($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where['apply.master_id'] = $master['id'];
        $where['apply.status'] = array('neq',9);
        $where['social.status'] = array('neq',9);
        $field = 'apply.id as apply_id, apply.type, apply.create_time, apply.apply_status, social.social_name, social.social_head_pic';
        $order = 'create_time desc';
        $result = D('Social') ->selectSocial($where, $field, $order, $request['p']);
        if(!$result){
            $result = array();
        }else{
            foreach($result as $k => $v){
                unset($head_pic);
                $head_pic = $this ->searchPhoto($v['social_head_pic']);
                $result[$k]['social_head_pic'] = $head_pic?$head_pic:'';
                $result[$k]['create_time'] = date('Y-m-d',$v['create_time']);
            }
        }

        apiResponse('1','',$result);
    }

    /*
    * 创建协会
    * 用户ID   账号token
    */
    public function createSocial($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where['master_id'] = $master['id'];
        $where['status'] = array('lt',5);
        $social = $this ->easyMysql('Social','3',$where);
        if($social){
            apiResponse('0','每个人只能建立或加入一个协会');
        }

        if($_FILES['social_head_pic']['name'] || $_FILES['social_pic']['name']){
            $res_pic = api('UploadPic/upload', array(array('save_path' => "Social")));
            $social_pic = array();
            foreach ($res_pic as $k => $value) {
                if($value['key'] == 'social_head_pic'){
                    $data['social_head_pic'] = $value['id'];
                }
                if($value['key'] == 'social_pic'){
                    $social_pic[] = $value['id'];
                }
            }
            $data['social_pic'] = implode(',',$social_pic);
        }

        $data['master_id']   = $master['id'];
        $data['social_name'] = $request['social_name'];
        $data['social_info'] = $request['social_info'];
        $data['address']     = $request['address'];
        $data['start_time']  = $request['start_time'];
        $data['one_contact'] = $request['one_contact'];
        $data['two_contact'] = $request['two_contact'];
        $data['create_time'] = time();
        $res = $this ->easyMysql('Social','1','',$data);
        if(!$res){
            apiResponse('0','申请创建协会失败');
        }
        unset($data);
        $data['social_id'] = $res;
        $data['master_id'] = $master['id'];
        $data['type']      = 1;
        $data['position']  = 1;
        $data['apply_status'] = 0;
        $data['create_time'] = time();
        $data['accept_time'] = time();
        $result = $this ->easyMysql('SocialApply','1','',$data);
        if(!$result){
            apiResponse('0','应该不会报这个错误');
        }
        apiResponse('1','申请成功');
    }

    /*
    * 协会相册
    * 用户ID   账号token
    */
    public function socialAlbum($request = array()){
        $where['id'] = $request['social_id'];
        $where['status'] = array('neq',9);
        $field = 'social_pic';
        $social = $this ->easyMysql('Social','3',$where);
        if(!$social){
            apiResponse('0','协会状态有误');
        }
        if($social['social_pic'] != ''){
            $picture = array();
            $social_pic = explode(',',$social['social_pic']);
            foreach($social_pic as $k => $v){
                unset($pic);
                $path = $this ->easyMysql('File','5',array('id'=>$v),'','path');
                $pic = $path?C('API_URL').$path:'';
                $picture[$k]['pic'] = $pic;
                $picture[$k]['picture_id'] = $v.'';
            }
        }else{
            $picture = array();
        }
        apiResponse('1','',$picture);
    }

    /*
    * 上传相册
    * 用户ID   账号token
    */
    public function addAlbum($request = array()){
        $where['id'] = $request['social_id'];
        $where['status'] = array('neq',9);
        $social = $this ->easyMysql('Social','3',$where);
        if(!$social){
            apiResponse('0','协会信息有误');
        }
        //发布图片不能为空w_r_pic0 w_r_pic1 w_r_pic2
        if (!empty($_FILES['social_pic']['name'])) {
            $res = api('UploadPic/upload', array(array('save_path' => "Social")));
            $social_pic = array();
            foreach ($res as $k => $value) {
                $social_pic[] = $value['id'];
            }

            $picture = implode(',',$social_pic);
            if(!$picture){
                apiResponse('0', '上传图片失败',$picture);
            }
        }

        if($social['social_pic'] != ''){
            $data['social_pic'] = $social['social_pic'].','.$picture;
        }else{
            $data['social_pic'] = $picture;
        }
        $data['update_time'] = time();
        $result = $this ->easyMysql('Social','2',$where,$data);
        if(!$result){
            apiResponse('0','上传相册失败');
        }
        apiResponse('1','上传成功');
    }

    /*
    * 协会成员
    * 用户ID   账号token
    */
    public function socialPeople($request = array()){
        if($request['nickname']){
            $where['master.nickname'] = array('like','%'.$request['nickname'].'%');
        }
        $where['apply.social_id'] = $request['social_id'];
        $where['apply.apply_status'] = 1;
        $where['apply.status'] = array('neq',9);
        $field = 'apply.id as people_id, apply.master_id, master.head_pic, master.nickname, apply.position';
        $order = 'apply.position asc, apply.create_time asc';

        $res = D('Social') ->selectPeople($where,$field,$order);
        if(!$res){
            $res = array();
        }else{
            foreach($res as $k => $v){
                $path = $this ->easyMysql('File','5',array('id'=>$v['head_pic']),'','path');
                $res[$k]['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Master/default.png';
            }
        }

        apiResponse('1','',$res);
    }

    /*
    * 协会详情
    * 用户ID   账号token
    */
    public function socialInfo($request = array()){
        //获取大师的信息
        $master = $this ->searchMaster($request['token']);
        //获取协会的基本信息
        $where['id'] = $request['social_id'];
        $where['status'] = array('neq',9);
        $field = 'id as social_id, social_name, social_head_pic, social_info, social_pic';
        $social = $this ->easyMysql('Social','3',$where,'',$field);
        if(!$social){
            apiResponse('0','协会信息有误');
        }
        $head_path = $this ->easyMysql('File','5',array('id'=>$social['social_head_pic']),'','path');
        $social['social_head_pic'] = $head_path?C('API_URL').$head_path:C('API_URL').'Uploads/Social/default.png';

        //获取协会的四张图片
        $social_pic = explode(',',$social['social_pic']);

        $picture = array();
        foreach($social_pic as $k => $v){
            unset($path);
            if($v != ''){
                $path = $this ->easyMysql('File','5',array('id'=>$v),'','path');
                $picture[$k]['pic'] = C('API_URL').$path;
                $picture[$k]['picture_id'] = $v.'';
            }else{
                break;
            }

            if($k == 4){
                break;
            }
        }

        $social['social_picture'] = $picture?$picture:array();
        //获取协会的人数以及大师本人是否在那个协会当中
        unset($where);
        $where['social_id'] = $request['social_id'];
        $where['apply_status'] = 1;
        $where['status'] = array('neq',9);
        $master_num = $this ->easyMysql('SocialApply','6',$where);
        $social['master_num'] = $master_num?$master_num.'':'0';
        $where['master_id'] = $master['id'];
        unset($where['apply_status']);
        $relation = $this ->easyMysql('SocialApply','3',$where);
        $social['relation'] = $relation?'1':'2';
        $social['type'] = $relation?$relation['type']:'0';
        $social['apply_status'] = $relation?$relation['apply_status']:'5';
        //获取该协会的前6个人
        unset($where);
        $where['apply.social_id'] = $request['social_id'];
        $where['apply.apply_status'] = 1;
        $where['apply.status'] = array('neq',9);
        $field = 'apply.id as apply_id, apply.master_id, apply.position, master.nickname, master.head_pic';
        $order = 'apply.position desc, apply.create_time desc';
        $limit = '6';
        $list = D('Social') ->selectPeople($where, $field, $order, $limit);
        if(!$list){
            $list = array();
        }else{
            foreach($list as $k =>$v){
                $master_path = $this ->easyMysql('File','5',array('id'=>$v['head_pic']),'','path');
                $list[$k]['master_head_pic'] = $master_path?C('API_URL').$master_path:C('API_URL').'/Uploads/Master/default.png';
            }
        }
        $social['master_list'] = $list;
        apiResponse('1','',$social);
    }

    /*
    * 退出协会
    * 用户ID   账号token
    */
    public function quitSocial($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where['master_id'] = $master['id'];
        $where['social_id'] = $request['social_id'];
        $where['status']    = array('neq',9);
        $res = $this ->easyMysql('SocialApply','3',$where);
        if(!$res){
            apiResponse('0','你并不在这个协会中');
        }
        $data['status'] = 9;
        $data['update_time'] = time();
        $result = $this ->easyMysql('SocialApply','2',$where,$data);
        if(!$result){
            apiResponse('0','退出协会失败');
        }

        $dat['social_id'] = 0;
        $dat['update_time'] = time();
        $master_social = $this ->easyMysql('Master',2,array('id'=>$master['id']),$dat);
        apiResponse('1','退出协会成功');
    }

    /*
     * 协会管理
     */
    public function socialManage($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where['master_id'] = $master['id'];
        $where['apply_status'] = 1;
        $where['status'] = array('neq',9);
        $result = $this ->easyMysql('SocialApply',3,$where);
        if($result){
            $status = '1';
        }
        $where['apply_status'] = '0';
        $res = $this ->easyMysql('SocialApply',3,$where);
        if($res && empty($status)){
            $status = '2';
        }
        if($status != 1&&$status != 2){
            $status = '3';
        }

        apiResponse('1','',$status);
    }
}