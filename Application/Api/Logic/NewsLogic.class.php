<?php
namespace Api\Logic;
/**
 * Class NewsLogic
 * @package Api\Logic
 * 新闻模块
 */
class NewsLogic extends BaseLogic{
    /**
     * 大师—新闻类别表
     */
    public function newsType(){
        $where['status'] = array('neq',9);
        $result = $this ->easyMysql('NewsType','4',$where,'','id as news_type_id, type_name','sort desc, create_time desc');
        if(!$result){
            $result = array();
        }
        apiResponse('1','',$result);
    }
    /**
     * 添加新闻
     */
    public function addNews($request = array()){
        $master = $this ->searchMaster($request['token']);
        $data['master_id'] = $master['id'];
        $data['title']   = $request['title'];
        $data['content'] = $request['content'];
        $data['news_type'] = $request['news_type'];
        //上传图片可以为空
        if(!empty($_FILES['picture']['name'])){
            $res = api('UploadPic/upload', array(array('save_path' => 'News')));
            foreach ($res as $value) {
                $head_pic = $value['id'];
                $data['picture'] = $head_pic;
            }
        }
        $data['create_time'] = time();
        $data['status']      = 1;
        $result = $this ->easyMysql('News','1','',$data);
        if(!$result){
            apiResponse('0','发布新闻失败');
        }
        apiResponse('1','发布新闻成功');
    }

    /**
     * 用户端—新闻列表
     * 新闻类别        news_type_id
     * 分页参数        p
     */
    public function newsList($request = array()){

        $news_type = $this ->easyMysql('NewsType','4',array('status'=>array('neq',9)),'','id as news_type_id, type_name','sort desc, create_time desc');
        if(!$news_type){
            $news_type = array();
        }
        $row_two['news_type_id'] = '99999';
        $row_two['type_name'] = '全部';
        array_unshift($news_type,$row_two);

        $result['news_type'] = $news_type;
        //获取新闻信息
        //获取新闻类别
        if(!empty($request['news_type_id'])){
            if($request['news_type_id'] != '99999'){
                $where['news.news_type'] = $request['news_type_id'];
            }
        }
        $where['news.status'] = array('neq',9);
        $field = 'news.id as news_id, news.title, news.picture, news.create_time, news.browse_times, news_type.type_name';
        $order = 'news.sort desc, news.create_time';
        $news = D('News') ->selectNews($where, $field ,$order,'',$request['p']);
        if(!$news){
            $news = array();
        }else{
            foreach($news as $k => $v){
                unset($picture);
                $picture = $this ->searchPhoto($v['picture']);
                $news[$k]['news_pic'] = $picture?$picture:'';
                $news[$k]['create_time'] = date('Y.m.d',$v['create_time']);
            }
        }

        $result['news'] = $news;
        apiResponse('1','',$result);
    }

    /**
     * 用户端—新闻详情
     */
    public function newsInfo($request = array()){
        $where['news.id'] = $request['news_id'];
        $where['news.status'] = array('neq',9);
        $field = 'news.id as news_id, news.master_id, news.title, news.content,news.picture, news.browse_times, news.create_time, master.nickname';
        $news = D('News') ->selectNews($where,$field,'',1);
        if(!$news){
            apiResponse('0','新闻信息有误');
        }else{
            $picture = $this ->searchPhoto($news['picture']);
            $news['news_pic'] = $picture?$picture:'';
            $news['create_time'] = date('Y.m.d',$news['create_time']);
            //给新闻关注度加1
            $res = $this ->setType('News',array('id'=>$request['news_id']),'browse_times','1','1');
        }

        apiResponse('1','',$news);
    }


    /**
     * 大师端—新闻列表
     */
    public function masterNewsList($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where  = array('news.master_id'=>$master['id'],'news.status'=>array('neq',9));
        $field = 'news.id as news_id, news.title, news.picture, news.create_time, news.browse_times, news_type.type_name';
        $order = 'news.sort desc, news.create_time';
        $news = D('News') ->selectNews($where, $field ,$order,'',$request['p']);
        if(!$news){
            $news = array();
        }else{
            foreach($news as $k => $v){
                unset($picture);
                $picture = $this ->searchPhoto($v['picture']);
                $news[$k]['news_pic'] = $picture?$picture:'';
                $news[$k]['create_time'] = date('Y.m.d',$v['create_time']);
            }
        }

        $result['news'] = $news;
        apiResponse('1','',$result);
    }

    /**
     * 大师端—删除新闻
     */
    public function deleteNews($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where  = array('id'=>$request['news_id'],'status'=>array('neq',9),'master_id'=>$master['id']);
        $news   = $this ->easyMysql('News',3,$where);
        if(!$news){
            apiResponse('0','新闻信息有误');
        }
        $data['status'] = 9;
        $data['update_time'] = time();
        $res = $this ->easyMysql('News',2,$where,$data);
        if(!$res){
            apiResponse('0','删除新闻失败');
        }
        apiResponse('1','删除成功');
    }

    /**
     * 大师端—新闻详情
     */
    public function masterNewsInfo($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where  = array('news.id'=>$request['news_id'],'news.status'=>array('neq',9),'news.master_id'=>$master['id']);
        $field  = 'news.id as news_id, news_type.type_name, news.title, picture, content';
        $news   = D('News') ->selectNews($where, $field ,'','1','');
        if(!$news){
            apiResponse('0','新闻信息有误');
        }
        $picture = $this ->searchPhoto($news['picture']);
        $news['news_pic'] = $picture?$picture:'';
        apiResponse('1','',$news);
    }

    /**
     * 大师端—新闻详情
     * 大师token    token
     * 新闻id       news_id
     * 新闻标题     title
     * 新闻内容     content
     * 新闻类别id   news_type
     * 上传图片     picture
     */
    public function modifyNewsInfo($request = array()){
        $master = $this ->searchMaster($request['token']);
        $where  = array('id'=>$request['news_id'],'status'=>array('neq',9),'master_id'=>$master['id']);
        $news   = $this ->easyMysql('News',3,$where);
        if(!$news){
            apiResponse('0','新闻信息有误');
        }
        if($request['title']){
            $data['title'] = $request['title'];
        }
        if($request['content']){
            $data['content'] = $request['content'];
        }
        if($request['news_type']){
            $data['news_type'] = $request['news_type'];
        }
        //上传图片可以为空
        if(!empty($_FILES['picture']['name'])){
            $res = api('UploadPic/upload', array(array('save_path' => 'News')));
            foreach ($res as $value) {
                $head_pic = $value['id'];
                $data['picture'] = $head_pic;
            }
        }
        $data['update_time'] = time();
        $result = $this ->easyMysql('News',2,$where,$data);
        if(!$result){
            apiResponse('0','修改新闻失败');
        }

        apiResponse('1','修改成功');
    }
}