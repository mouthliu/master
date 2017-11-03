<?php

namespace Manager\Controller;


/**
 * [统计]
 * @author zhouwei
 * Class StatisController
 * @package Manager\Controller
 */
class StatisController extends BaseController
{
    /**
     * 仪表盘首页
     */
    public function index()
    {
        $request = $_POST;
        if( !empty($request['startTime']) && !empty($request['endTime']) ){
            $startTime = strtotime($request['startTime']);
            $endTime = strtotime($request['endTime']);
        }else{
            $startTime = strtotime('-1 week');
            $endTime = time();
        }
        $time = M('member') -> where(array('satatus'=>array('neq',9))) -> field('MIN(create_time) as min_time,MAX(create_time) as max_time')-> find();
        $time['min_time'] = date('Y-m-d',$time['min_time']);
        $time['max_time'] = date('Y-m-d',$time['max_time']);
        $x_res = D('Statis','Service')->createX($startTime,$endTime);
        $this->assign('totalTime',$time); // 统计最大注册时间和最小注册时间
        $this->everyDay($startTime,$endTime);
        $this->assign('x_date',$x_res['x_date']);
        $this -> display();
    }

    /**
     * 统计 按照条件生成 线形图
     * @param $start_time
     * @param $end_time
     * @param $result
     */
    public function everyDay($start_time,$end_time){

        $where['status'] = array('neq',9); // 判断用户是否被删除
        $RegFromTime = array(
            array('title'=>'人数','where'=>$where,'obj'=>M('member'),'flag'=>array('Count','id'))
        );
        //数据参数
        $line_parameter = $RegFromTime;
        //获取数据
        $sales_line_data = D('Statis','Service')->getLineData($start_time,$end_time,$line_parameter);
        //创建折线
        $this->assign('day_line',D('Statis','Service')->createLine($sales_line_data));
        //顶部文字subtitle
        $this->assign('day_date_flag','【平台注册人数统计】　'.date('Y-m-d',$start_time).'至'.date('Y-m-d',$end_time).'注册人数)');
    }
}