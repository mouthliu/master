<?php
namespace Common\Service;
use Think\Model;

/**
 * [仪表盘]
 * @author zhouwei
 * Class StatisService
 * @package Common\Service
 */
class StatisService extends BaseService
{
    /**
     * 创建横坐标
     * @param $start_date  开始时间  时间戳
     * @param $end_date  结束时间
     * @return array
     */
    public function createX($start_date,$end_date){
        //连个时间相同  就是一天
        if($start_date == $end_date){
            $day = 1;
        }else{
            $day = ($end_date-$start_date)%86400 > 0 ? floor(($end_date-$start_date)/86400)+1 : floor(($end_date-$start_date)/86400);
        }
        //创建横坐标显示
        $date = "";
        for($i = 0; $i <= $day; $i++){
            $d = date('y/m/d',intval($start_date) + intval($i*86400));
            $date .= "'$d',";
        }
        $x_date = substr($date,0,strlen($date)-1);
        //横坐标区间
        $step = floor($day/15);
        return array('x_date'=>$x_date,'step'=>$step);
    }

    /**
     * 获取折线统计数据
     * @param $start_date
     * @param $end_date
     * @param $parameter  相关参数 包含 (标题  查询条件  对象)
     * @return mixed
     */
    public function getLineData($start_date,$end_date,$parameter){
        if($start_date == $end_date){
            $day = 1;
        }else{
            //获取天数
            $day = ($end_date-$start_date)%86400 > 0 ? floor(($end_date-$start_date)/86400)+1 : floor(($end_date-$start_date)/86400);
        }
        //获取统计值
        for($i = 0; $i <= $day; $i++){
            foreach($parameter as $k => $value){
                $value['where']['create_time'] = array('between',($start_date + $i * 86400).",".($start_date + ($i+1) * 86400));

                if(empty($value['flag'])){
                    $data[$k][] = $value['obj']->where($value['where'])->count();
                }else{
                    $field = "COUNT(".$value['flag'][1].")";
                    $amount = $value['obj']->where($value['where'])->getField($field);
                    $data[$k][] =  sprintf("%.2f", $amount);
                }
            }
        }
        //添加标题
        foreach($parameter as $k => $value){
            $result[$value['title']] = $data[$k];
        }
        return $result;
    }

    /**
     * 折线参数处理
     * 2014-6-3
     * @param $data 数据格式   $data["商铺统计 **折线名称**"] = array(4,5,...)数组中存入每个时间段的统计数量;
     * @return string
     */
    public function createLine($data){
        //创建折线参数字符串
        $line = '';
        foreach($data as $key => $value){
            $line_data = '';
            $line.= "{type:'line',name: '".$key."',data:[";
            foreach($value as $v){
                $line_data.=$v.',';
            }
            $line.=substr($line_data,0,strlen($line_data)-1);
            $line.="]},";
        }
        //去除字符串末尾的逗号
        $line = substr($line,0,strlen($line)-1);
        //返回highcharts格式的字符串
        return $line;
    }

}