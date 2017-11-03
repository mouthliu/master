<?php
namespace Api\Logic;
/**
 * Class ArticleLogic
 * @package Api\Logic
 * 文章系统
 */
class ArticleLogic extends BaseLogic{

    /**
     * 围观说明
     */
    public function onlookers(){
        $content = M('Article')->where(array('id'=>1))->getField('content');
        $content = $content?$content:'';
        preg_match_all('/src=\"\/?(.*?)\"/',$content,$match);
        foreach($match[1] as $key => $src){
            if(!strpos($src,'://')){
                $content = str_replace('/'.$src,C('API_URL')."/".$src."\" width=100%",$content);
            }
        }
        $result_data['content'] = $content;
        apiResponse('1','',$result_data);
    }

    /**
     * 填写说明
     */
    public function writing(){
        $content = M('Article')->where(array('id'=>3))->getField('content');
        $content = $content?$content:'';
        preg_match_all('/src=\"\/?(.*?)\"/',$content,$match);
        foreach($match[1] as $key => $src){
            if(!strpos($src,'://')){
                $content = str_replace('/'.$src,C('API_URL')."/".$src."\" width=100%",$content);
            }
        }
        $result_data['content'] = $content;
        apiResponse('1','',$result_data);
    }

    /**
     * 帮助中心
     */
    public function requiredRead(){
        $content = M('Article')->where(array('id'=>2))->getField('content');
        $content = $content?$content:'';
        preg_match_all('/src=\"\/?(.*?)\"/',$content,$match);
        foreach($match[1] as $key => $src){
            if(!strpos($src,'://')){
                $content = str_replace('/'.$src,C('API_URL')."/".$src."\" width=100%",$content);
            }
        }
        $result_data['content'] = $content;
        apiResponse('1','',$result_data);
    }


    /**
     * 用户使用协议
     */
    public function memberAgreement(){
        $content = M('Article')->where(array('id'=>7))->getField('content');
        $content = $content?$content:'';
        preg_match_all('/src=\"\/?(.*?)\"/',$content,$match);
        foreach($match[1] as $key => $src){
            if(!strpos($src,'://')){
                $content = str_replace('/'.$src,C('API_URL')."/".$src."\" width=100%",$content);
            }
        }
        $result_data['content'] = $content;
        apiResponse('1','',$result_data);
    }
}