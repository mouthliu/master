<?php
namespace Api\Logic;
/**
 * Class FeedbackLogic
 * @package Api\Logic
 * 意见反馈模块
 */
class FeedbackLogic extends BaseLogic{
    /*
     * 意见反馈内容
     * */
    public function feedback($request = array()){
        if(empty($request['content'])){
            apiResponse('error','意见反馈不能为空');
        }
        //新增意见反馈
        $data['content']     = $request['content'];
        $data['create_time'] = time();
        $result = M('Feedback') ->add($data);
        if(!$result){
            apiResponse('error','意见反馈失败');
        }
        apiResponse('success','意见反馈成功');
    }
}