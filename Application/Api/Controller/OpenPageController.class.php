<?php
namespace Api\Controller;
use Think\Controller;

class OpenPageController extends BaseController{

    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 开启页
     */
   public function getPic(){
       $where['id'] = 1;
       $picture = M('OpenPage')->where($where)->getField('picture');
       if($picture){
           $path = M('File')->where(array('id'=>$picture))->getField('path');
           if($path){
               $result_data['picture'] = C('API_URL').$path;
           }else{
               $result_data['picture'] = '';
           }
       }else{
           $result_data['picture'] = '';
       }
       apiResponse('success','请求成功',$result_data);
   }
}