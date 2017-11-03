<?php
namespace Home\Controller;

class IndexController extends BaseController{

    public function _initialize(){
        parent::_initialize();
    }
    function index() {
    	$this->display('index');
    }
}