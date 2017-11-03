<?php
namespace Manager\Controller;

/**
 * Class HomePageController
 * @package Manager\Controller
 * =首页控制器
 */
class HomePageController extends BaseController {

    function getIndexRelation() {

    }

    /**
     * 修改时关联数据
     */
    function getUpdateRelation() {
        $this->assign('ad_position',C('AD_POSITION'));
    }

    /**
     * 新添时关联数据
     */
    function getAddRelation() {
        $this->assign('ad_position',C('AD_POSITION'));
    }
}