<?php
namespace Api\Model;

/**
 * 协会模块类
 */
class SocialModel extends BaseModel{
    public function getList($param = array()){

    }
    public function findRow($param = array()){

    }
    public function selectSocial($where,$field,$order,$page){
        $result = M('Social') ->alias('social') ->where($where)
            ->join(array(
                'LEFT JOIN db_social_apply apply ON apply.social_id = social.id',
            ))
            ->field($field) ->order($order) ->page($page,10) ->select();
        return $result;
    }

    public function selectPeople($where = array(), $field = '*', $order = '',$limit = '', $page = ''){
        if(empty($limit)){
            $result = M('SocialApply') ->alias('apply') ->where($where)
                ->join(array(
                    'LEFT JOIN db_master master ON master.id = apply.master_id',
                ))
                ->field($field) ->order($order) ->page($page, 10) ->select();
        }else{
            $result = M('SocialApply') ->alias('apply') ->where($where)
                ->join(array(
                    'LEFT JOIN db_master master ON master.id = apply.master_id',
                ))
                ->field($field) ->limit($limit) ->select();
        }

        return $result;
    }
}