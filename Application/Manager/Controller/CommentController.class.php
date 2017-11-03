<?php
namespace Manager\Controller;
/**
 * Class CommentController
 * @package Manager\Controller
 * 评价控制器
 */
class CommentController extends BaseController{
    /**
     * 查看评价详情
     */
    public function detail(){
        $this->assign('row',D('Comment','Logic')->findRow(I('get.')));
        $this->display('detail');
    }
}