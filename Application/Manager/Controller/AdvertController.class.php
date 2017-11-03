<?php
namespace Manager\Controller;

/**
 * Class AdvertController
 * @package Manager\Controller
 * 广告控制器
 */
class AdvertController extends BaseController {

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
