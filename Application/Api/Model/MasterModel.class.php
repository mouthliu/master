<?php
namespace Api\Model;
use Think\Model;

/**
 * Class BaseModel
 * @package Manager\Model
 * 大师类
 */
class MasterModel extends BaseModel {
    function getList($param = array()){

    }

    function findRow($param = array()){

    }

    public function typeMaster($where = array(), $field = '*',$order = '',$page = ''){
        if(empty($page)){
            $result = M('MasterService') ->alias('m_s') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = m_s.master_id',
                    'LEFT JOIN '.C('DB_PREFIX').'service service ON service.id = m_s.service_id',
                ))
                ->field($field) ->order($order) ->group('m_s.master_id') ->select();
        }else{
            $result = M('MasterService') ->alias('m_s') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master master ON master.id = m_s.master_id',
                    'LEFT JOIN '.C('DB_PREFIX').'service service ON service.id = m_s.service_id',
                ))
                ->field($field) ->order($order) ->page($page, 10) ->group('m_s.master_id') ->select();
        }

        return $result;
    }

    public function selectMaster($where = array(), $field = '*',$order = '', $page = ''){
        if(empty($page)){
            $result = M('Master') ->alias('master') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master_service m_s ON m_s.master_id = master.id',
                    'LEFT JOIN '.C('DB_PREFIX').'service service ON service.id = m_s.service_id',
                ))
                ->field($field) ->order($order) ->select();
        }else{
            $result = M('Master') ->alias('master') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'master_service m_s ON m_s.master_id = master.id',
                    'LEFT JOIN '.C('DB_PREFIX').'service service ON service.id = m_s.service_id',
                ))
                ->field($field) ->order($order) ->page($page, 10) ->select();
        }

        return $result;
    }

    public function showMaster($where = array(), $field = '*',$order = '', $page = ''){
        $result = M('Master') ->alias('master') ->where($where) ->field($field) ->order($order) ->page($page, 10) ->select();
        return $result;
    }

    public function showService($where = array(), $field = '*',$order = '', $page = ''){
        if(empty($page)){
            $result = M('MasterService') ->alias('m_s') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'service service ON service.id = m_s.service_id',
                ))
                ->field($field) ->order($order) ->select();
        }else{
            $result = M('MasterService') ->alias('m_s') ->where($where)
                ->join(array(
                    'LEFT JOIN '.C('DB_PREFIX').'service service ON service.id = m_s.service_id',
                ))
                ->field($field) ->order($order) ->page($page, 10) ->select();
        }

        return $result;
    }

    public function foreachMaster($master){

        foreach($master as $k => $v){
            unset($order_num);
            unset($field_info);
            $head_pic = D('Index','Logic') ->searchPhoto($v['head_pic']);
            $master[$k]['head_pic'] = $head_pic?$head_pic:C('API_URL').'/Uploads/Master/default.png';
            if($v['social_id'] == '0'){
                $master[$k]['social_status'] = '2';
            }else{
                $master[$k]['social_status'] = '1';
            }

            if(!empty($v['field_id'])){
                $field_list = explode(',',$v['field_id']);

                $field_info = array();
                foreach($field_list as $key =>$val){

                    $field_name = D('Index','Logic') ->easyMysql('Field',3,array('id'=>$val,'status'=>array('neq',9)),'','id as field_id, field_name');
                    if(!empty($field_name)){
                        $field_info[] = $field_name;
                    }
                }
            }
            $master[$k]['field_info'] = $field_info?$field_info:array();

            $order_num = D('Index','Logic') ->serviceOrderNum($v['master_id']);
            $master[$k]['order_num'] = $order_num?$order_num.'':'0';
        }

        return $master;
    }
}