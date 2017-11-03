<?php
namespace Api\Logic;
/**
 * Class MemberLogic
 * @package Api\Logic
 * 收藏模块
 */
class CollectLogic extends BaseLogic{

    /**
     * 我的收藏列表
     */
    public function myCollectList($request = array()){
        $member = $this ->searchMember($request['token']);
        $where  = array('collect.m_id'=>$member['id'], 'collect.status'=>array('neq',9), 'goods.status'=>1, 'master.status'=>1, 'goods.frame'=>1);
        $field  = 'collect.id as collect_id, collect.goods_id, goods.goods_name, goods.goods_pic, goods.price, master.nickname, master.head_pic, master.auth_status, master.social_id';
        $order  = 'collect.create_time desc';
        $collect = D('Collect') ->searchCollect($where, $field, $order, $request['p']);
        if(!$collect){
            $collect = array();
        }else{
            foreach($collect as $k => $v){
                unset($head_pic);
                unset($picture);
                $head_pic = $this ->searchPhoto($v['head_pic']);
                $picture  = $this ->searchPhoto($v['goods_pic']);
                $collect[$k]['head_pic'] = $head_pic?$head_pic:C('API_URL').'/Uploads/Master/default.png';
                $collect[$k]['goods_pic'] = $picture?$picture:'';
                if($v['social_id'] != 0){
                    $collect[$k]['social_status'] = '1';
                }else{
                    $collect[$k]['social_status'] = '2';
                }
                $order_num = $this ->getOrderNum($v['goods_id']);
                $collect[$k]['order_num'] = $order_num?$order_num.'':'0';
            }
        }

        apiResponse('1','',$collect);
    }

    /**
     * 收藏商品
     */
    public function collectGoods($request = array()){
        $member = $this ->searchMember($request['token']);
        $where  = array('m_id' =>$member['id'], 'goods_id'=>$request['goods_id'], 'status'=>array('neq',9));
        $collect = $this ->easyMysql('Collect', '3', $where);
        if($collect){
            apiResponse('0','该商品您已收藏');
        }
        $data['m_id'] = $member['id'];
        $data['goods_id'] = $request['goods_id'];
        $data['create_time'] = time();
        $result = $this ->easyMysql('Collect', '1', '', $data);
        if(!$result){
            apiResponse('0','收藏商品失败');
        }
        apiResponse('1','收藏商品成功');
    }

    /**
     * 收藏商品
     */
    public function concellCollect($request = array()){
        $member = $this ->searchMember($request['token']);
        $where  = array('m_id' =>$member['id'], 'goods_id'=>$request['goods_id'], 'status'=>array('neq',9));
        $collect = $this ->easyMysql('Collect', '3', $where);
        if(!$collect){
            apiResponse('0','该商品您还未收藏');
        }
        $data['status'] = 9;
        $data['update_time'] = time();
        $result = $this ->easyMysql('Collect', '2', $where, $data);
        if(!$result){
            apiResponse('0','取消收藏失败');
        }
        apiResponse('1','取消收藏成功');
    }

    /**
     * 删除收藏
     */
    public function deleteCollect($request = array()){
        $member = $this ->searchMember($request['token']);
        $collect = explode(',',$request['collect_id']);
        if(!$collect){
            apiResponse('0','收藏ID格式有误');
        }
        $where  = array('id'=>array('IN',$collect), 'm_id'=>$member['id'], 'status'=>array('neq',9));
        $data['status'] = 9;
        $data['update_time'] = time();
        $result = $this ->easyMysql('Collect', '2', $where, $data);
        if(!$result){
            apiResponse('0','取消收藏失败');
        }
        apiResponse('1','取消收藏成功');
    }

    /**
     * 我的关注列表
     */
    public function myFollowList($request = array()){
        $member = $this ->searchMember($request['token']);
        $where  = array('follow.m_id'=>$member['id'], 'follow.status'=>array('neq',9), 'master.status'=>1);
        $field  = 'follow.id as follow_id, follow.master_id, master.nickname, master.head_pic, master.introduction, master.field_id, master.social_id, master.auth_status, master.score';
        $order  = 'follow.create_time desc';
        $follow = D('Collect') ->searchFollow($where, $field, $order, $request['p']);
        if(!$follow){
            $follow = array();
        }else{
            foreach($follow as $k => $v){
                unset($head_pic);
                $head_pic = $this ->searchPhoto($v['head_pic']);
                $follow[$k]['head_pic'] = $head_pic?$head_pic:C('API_URL').'/Uploads/Master/default.png';
                if($v['social_id'] != 0){
                    $follow[$k]['social_status'] = '1';
                }else{
                    $follow[$k]['social_status'] = '2';
                }
                $order_num = $this ->serviceOrderNum($v['master_id']);
                $follow[$k]['order_num'] = $order_num?$order_num.'':'0';

                if(!empty($v['field_id'])){
                    $field_list = explode(',',$v['field_id']);

                    $field_info = array();
                    foreach($field_list as $key =>$val){

                        $field_name = D('Index','Logic') ->easyMysql('Field',3,array('id'=>$val,'status'=>array('neq',9)),'','id as field_id, field_name');
                        if(!empty($field_name)){
                            $field_info[] = $field_name;
                        }
                    }
                }
                $follow[$k]['field_info'] = $field_info?$field_info:array();
            }
        }

        apiResponse('1','',$follow);
    }

    /**
     * 关注大师
     */
    public function followMaster($request = array()){
        $member = $this ->searchMember($request['token']);
        $where  = array('master_id'=>$request['master_id'], 'm_id'=>$member['id'], 'status'=>array('neq',9));
        $follow = $this ->easyMysql('Follow','3',$where);
        if($follow){
            apiResponse('0','你已经关注了这位大师');
        }
        $data['m_id'] = $member['id'];
        $data['master_id'] = $request['master_id'];
        $data['create_time'] = time();
        $result = $this ->easyMysql('Follow','1','',$data);
        if(!$result){
            apiResponse('0','关注大师失败');
        }

        apiResponse('1','关注成功');
    }

    /**
     * 取消关注大师
     * 用户token    token   adf962e22137a8860ddf1f81bcc9c094
     * 大师ID       master_id
     */
    public function concellFollow($request = array()){
        $member = $this ->searchMember($request['token']);
        $where  = array('master_id'=>$request['master_id'], 'm_id'=>$member['id'], 'status'=>array('neq',9));
        $follow = $this ->easyMysql('Follow','3',$where);
        if(!$follow){
            apiResponse('0','您还未关注本大师');
        }
        $data['status'] = 9;
        $data['update_time'] = time();
        $result = $this ->easyMysql('Follow','2',$where,$data);
        if(!$result){
            apiResponse('0','取消关注失败');
        }

        apiResponse('1','取消关注成功');
    }

    /**
     * 取消关注
     */
    public function deleteFollow($request = array()){
        $member = $this ->searchMember($request['token']);
        $follow = explode(',',$request['follow_id']);
        if(!$follow){
            apiResponse('0','收藏ID格式有误');
        }
        $where  = array('id'=>array('IN',$follow), 'm_id'=>$member['id'], 'status'=>array('neq',9));
        $data['status'] = 9;
        $data['update_time'] = time();
        $result = $this ->easyMysql('Follow', '2', $where, $data);
        if(!$result){
            apiResponse('0','取消关注失败');
        }
        apiResponse('1','取消关注成功');
    }
}