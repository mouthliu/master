<?php
namespace Manager\Controller;

/**
 * Class MessageController
 * @package Manager\Controller
 * 后台信息控制器
 */
class MessageController extends BaseController {
    /**
     * 消息详情
     */
    public function detail(){
        $this->assign('row',D('Message','Logic')->findRow(I('get.')));
        $this->display('detail');
    }

    /**
     * 频道列表页
     */
    function message() {
        $this->checkRule(self::$rule);
        $Object = D(CONTROLLER_NAME,'Logic');
        $result = $Object->message(I('request.'));
        if($result) {
//         	dump($result['list']);
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
        } else {
            $this->error($Object->getLogicError());
        }
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->getIndexRelation();
        $this->display('message');
    }

    protected function getAddRelation() {}
    /**
     * 添加
     */
    function add() {
        $this->checkRule(self::$rule);
        if(!IS_POST) {
            $this->getAddRelation();
            $this->display('update');
        } else {
            $Object = D(CONTROLLER_NAME,'Logic');
            $result = $Object->update(I('post.'));
            if($result) {
                $this->success($Object->getLogicSuccess(), Cookie('__forward__'));
            } else {
                $this->error($Object->getLogicError());
            }
        }
    }
    function messageAdd()
    {
        $this->checkRule(self::$rule);
        if ($_GET) {
            $Object = D(CONTROLLER_NAME, 'Logic');
            $result = $Object->messageAdd(I('request.'));
            if($result) {
                $this->success($Object->getLogicSuccess(), Cookie('__forward__'));
            } else {
                $this->error($Object->getLogicError());
            }
        }
    }
}
