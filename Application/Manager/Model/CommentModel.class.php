<?php
namespace Manager\Model;
/**
 * Class CommentModel
 * @package Manager\Model
 * 评价
 */
class CommentModel extends BaseModel{
//    protected $trueTableName = 'txunda_goods_comment'; // 这里是 数据库调用
    /**
     * @param array $param  综合条件参数
     * @return array
     */
    function getList($param = array()) {
        if(!empty($param['page_size'])) {
            $total      = $this->alias('comment')->where($param['where'])
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = comment.m_id',
                    'LEFT JOIN '.C('DB_PREFIX').'member user ON user.id = comment.user_id',
                    'LEFT JOIN '.C('DB_PREFIX').'w_record record ON record.id = comment.w_r_id'
                ))
                ->count();
            $Page       = $this->getPage($total, $param['page_size'], $param['parameter']);
            $page_show  = $Page->show();
        }
        $model  = $this->alias('comment') ->where($param['where'])
            ->join(array(
                'LEFT JOIN '.C('DB_PREFIX').'member member ON member.id = comment.m_id',
                'LEFT JOIN '.C('DB_PREFIX').'member user ON user.id = comment.user_id',
                'LEFT JOIN '.C('DB_PREFIX').'w_record record ON record.id = comment.w_r_id'
            ))
            ->field('comment.*, member.nickname as member_nickname, user.nickname as user_nickname, record.title')
            ->order($param['order']);
        //是否分页
        !empty($param['page_size'])  ? $model = $model->limit($Page->firstRow,$Page->listRows) : '';

        $list = $model->select();
        return array('list'=>$list,'page'=>!empty($page_show) ? $page_show : '');
    }

    /**
     * @param array $param
     * @return mixed
     */
    function findRow($param = array()) {
        $row = $this->alias('comment')
                    ->field('comment.*,m.account,merchant.shop_name,employee.employee_name')
                    ->where($param['where'])
                    ->join(array(
                        'LEFT JOIN '.C('DB_PREFIX').'member m ON m.id = comment.m_id',
                        'LEFT JOIN '.C('DB_PREFIX').'merchant merchant ON merchant.id = comment.merchant_id',
                         )
                    )
                    ->find();
        $row['comment_picture'] = api('System/getFiles',array($row['picture'],array('id','path')));
        return $row;
    }
}