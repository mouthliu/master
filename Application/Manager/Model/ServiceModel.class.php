<?php
namespace Manager\Model;

/**
 * Class FileModel
 * @package Manager\Model
 * 服务类型模型
 */
class ServiceModel extends BaseModel {

    protected $_validate = array(
        array('picture', 'require', '请上传服务类别图片', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('ad_url','/http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/','链接格式不正确！',self::VALUE_VALIDATE,'regex'),
        array('title', 'require', '请填写服务类别', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('content', 'require', '请填写服务详情', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('is_show', 'require', '请选择是否在首页显示', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('price', 'require', '请填写默认价格', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
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
            $total      = $this ->alias('service') ->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        $model  = $this ->alias('service')
            ->field('service.*,f.path')
            ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'file f ON service.picture = f.id',
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
        $row = $this->alias('service')
            ->field('service.*')
            ->where($param['where'])
            ->find();
        return $row;
    }
}