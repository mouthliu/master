<?php
namespace Manager\Controller;

/**
 * Class CouponController
 * @package Manager\Controller
 * 优惠券控制器
 */
class CouponController extends BaseController {
    /**
     * 新添时关联数据
     */
    function getAddRelation() {
        $this->assign('coupon_type',C('COUPON_TYPE'));
    }

    /**
     * 添加
     */
    function add() {
        $this->checkRule(self::$rule);
        if(!IS_POST) {
            $this->assign('code',randomKey(5,8));
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

    /**
     * 发放优惠券
     */
    function grant() {
        $this->checkRule(self::$rule);
        $Object = D(CONTROLLER_NAME,'Logic');
        $result = $Object->grant(I('request.'));
        if($result) {
            $this->success($Object->getLogicSuccess());
        } else {
            $this->error($Object->getLogicError());
        }
    }
}
