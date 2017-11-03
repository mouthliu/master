<?php
namespace Manager\Model;

/**
 * Class HomePageModel
 * @package Manager\Model
 * 首页类别模型
 */
class HomePageModel extends BaseModel {


    protected $_validate = array(
        array('ad_pic', 'require', '请上传广告图片', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array (
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );

    /**
     * @param array $param  综合条件参数
     * @return array
     */
    function getList($param = array()) {
        if(!empty($param['page_size'])) {
            $total      = $this ->where($param['where']) ->count();
            $Page       = $this ->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page ->show();
        }
        $model  = $this->alias('merchant_type')
            ->field('merchant_type.*,f.path')
            ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'file f ON merchant_type.logo = f.id',
            ))
            ->order($param['order']);


        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();

        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    /**
     * @param $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this ->alias('merchant_type')
            ->field('merchant_type.*,f.path')
            ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'file f ON merchant_type.logo = f.id',
            ))
            ->find();
        return $row;
    }
}