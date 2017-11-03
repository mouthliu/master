<?php
namespace Manager\Model;

/**
 * Class GoodsModel
 * @package Manager\Model
 * 会员模型
 */
class GoodsModel extends BaseModel {

    /**
     * @var array
     * 自动验证规则
     */
    protected $_validate = array (
        array('price', 'require', '请填写商品价格', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('goods_name', 'require', '请填写商品名称', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('goods_pic', 'require', '商品图片不能为空', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('frame', 'require', '请填写商品上下架状态', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('first_type', 'require', '请选择一级分类', self::MUST_VALIDATE, 'regex', self::MODEL_INSERT),
        array('goods_type', 'require', '请选择二级分类', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('goods_info', 'require', '请填写商品描述', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('is_show', 'require', '请选择是否在首页进行显示', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('integral', 'require', '请填写商品购买可兑换积分', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
        array('freight', 'require', '请填写商品运费', self::MUST_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    /**
     * @var array
     * 自动完成规则
     */
    protected $_auto = array(
        array('create_time', 'time', self::MODEL_INSERT, 'function'),
        array('update_time', 'time', self::MODEL_UPDATE, 'function'),
    );

    function getList($param = array()) {
        if(!empty($param['page_size'])) {
            $total      = $this->alias('goods')->where($param['where'])->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        $model  = $this->alias('goods')
            ->field('goods.*, master.nickname')
            ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = goods.master_id',
//                'LEFT JOIN '.C('DB_PREFIX').'goods_type goods_type ON goods_type.id = goods.goods_type',
//                'LEFT JOIN '.C('DB_PREFIX').'goods_type first_type ON first_type.id = goods.first_type',
            ))
            ->order($param['order']);

        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();

        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    function findRow($param = array())
    {
        $row = $this->alias('goods')->where($param['where'])
            ->where($param['where'])
            ->join(array(
                'LEFT JOIN ' . C('DB_PREFIX') . 'master master ON master.id = goods.master_id',
//                'LEFT JOIN ' . C('DB_PREFIX') . 'goods_type goods_type ON goods_type.id = goods.goods_type',
            ))
            ->find();
        return $row;
    }
}