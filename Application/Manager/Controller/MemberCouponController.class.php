<?php
namespace Manager\Controller;

/**
 * Class MemberCouponController
 * @package Manager\Controller
 * 用户优惠券控制器
 */
class MemberCouponController extends BaseController {
    /**
     * 频道列表页
     */
    function memberindex() {
        $this->checkRule(self::$rule);
        $Object = D(CONTROLLER_NAME,'Logic');
        $result = $Object->memberGetList(I('request.'));
        if($result) {
//         	dump($result['list']);
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
        } else {
            $this->error($Object->getLogicError());
        }
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->display();
    }
}
