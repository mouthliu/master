<?php
namespace Manager\Controller;
/**
 * Class MemberController
 * @package Manager\Controller
 * 商品类别控制器
 */
class GoodsTypeController extends BaseController {
    /**
     * 频道列表页
     */
    function sonindex() {
        $this->checkRule(self::$rule);
        $Object = D(CONTROLLER_NAME,'Logic');
        $result = $Object->sonGetList(I('request.'));
        if($result) {
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
        } else {
            $this->error($Object->getLogicError());
        }
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->getIndexRelation();
        $this->display('sonindex');
    }
}