<?php
namespace Manager\Model;

/**
 * Class MessageModel
 * @package Manager\Model
 * 信息提现模型
 */
class NewsModel extends BaseModel {

    protected $_validate = array(
        array('picture', 'require', '请上传新闻图片', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('ad_url','/http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/','链接格式不正确！',self::VALUE_VALIDATE,'regex'),
        array('news_type', 'require', '请选择新闻类型', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH)
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
            $total      = $this->alias('news')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        $model  = $this->alias('news')
            ->field('news.*, master.nickname, news_type.type_name')
            ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = news.master_id',
                'LEFT JOIN '.C('DB_PREFIX').'news_type news_type ON news_type.id = news.news_type',
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
        $row = $this->alias('news')
            ->field('news.*, master.nickname')
            ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = news.master_id',
            ))
            ->find();
        return $row;
    }
}