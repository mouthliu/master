<?php
namespace Manager\Model;

/**
 * Class FileModel
 * @package Manager\Model
 * 广告模型
 */
class AdvertModel extends BaseModel {

    protected $_validate = array(
        array('ad_pic', 'require', '请上传广告图片', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('url','/http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/','链接格式不正确！',self::VALUE_VALIDATE,'regex'),
        array('type', 'require', '请选择轮播图类型', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH)
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
            $total      = $this->alias('ad')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        $model  = $this->alias('ad')
                        ->field('ad.*,f.path')
                        ->where($param['where'])
                        ->join(array(
                            'LEFT JOIN '.C('DB_PREFIX').'file f ON ad.ad_pic = f.id',
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
        $row = $this->alias('ad')
                    ->field('ad.*')
                    ->where($param['where'])
                    ->find();
        return $row;
    }
}