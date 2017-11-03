<?php
/**
 * 菜单配置列表
 * group  父菜单 title标题名称  icon改组图标 class是否选中 默认为空 url链接地址  is_developer 0都可见 1开发者模式可见
 * child 子菜单 同上
 */
    return array(
        'MENUS' => array(
//            array(
//                'group' => array('title' => '首页', 'icon' => 'icon-home', 'class' => '', 'url' => 'Index/index', 'is_developer' => 0),
//                '_child' => array()
//            ),
            array(
                'group' => array('title'=>'仪表盘','icon'=>'icon-dashboard','class'=>'','url'=>'Statis/index','is_developer'=>0),
            ),
            array(
                'group' => array('title'=>'会员管理','icon'=>' icon-user','class'=>'','url'=>'Member/index','is_developer'=>0),
            ),
            array(
                'group' => array('title'=>'大师管理','icon'=>'icon-user-md','class'=>'','url'=>'Master/index','is_developer'=>0),
            ),

            array(
                'group' => array('title'=>'协会管理','icon'=>'icon-comment','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
//                    array('title'=>'短信模板设置','url'=>'SendTemplate/index','class'=>'','is_developer'=>0),
//                    array('title'=>'短信发送记录','url'=>'SendLog/index','class'=>'','is_developer'=>0),
                    array('title'=>'协会列表','url'=>'Social/index','class'=>'','is_developer'=>0),
//                    array('title'=>'成员申请列表','url'=>'SocialApply/index','class'=>'','is_developer'=>0),
//                    array('title'=>'中奖订单','url'=>'LotteryOrder/index','class'=>'','is_developer'=>0),
                )
            ),

            array(
                'group' => array('title'=>'商品管理','icon'=>'icon-food','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'商品类别','url'=>'GoodsType/index','class'=>'','is_developer'=>0),
                    array('title'=>'商品列表','url'=>'Goods/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title'=>'服务管理','icon'=>'icon-reorder','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'服务类别','url'=>'Service/index','class'=>'','is_developer'=>0),
                    array('title'=>'服务订单','url'=>'ServiceOrder/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title'=>'订单管理','icon'=>' icon-th','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'用户订单','url'=>'Order/index','class'=>'','is_developer'=>0),
                    array('title'=>'悬赏订单','url'=>'RewardOrder/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title'=>'财务管理','icon'=>'icon-money','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'提现列表','url'=>'Withdraw/index','class'=>'','is_developer'=>0),
                    array('title'=>'充值类别表','url'=>'Recharge/index','class'=>'','is_developer'=>0),
                    array('title'=>'充值列表','url'=>'MemberRecharge/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title'=>'优惠券管理','icon'=>' icon-heart','class'=>'','url'=>'Coupon/index','is_developer'=>0),
            ),
        	array(
                'group' => array('title'=>'广告管理','icon'=>'icon-bullhorn','class'=>'','url'=>'Advert/index','is_developer'=>0),
            ),
            array(
                'group' => array('title'=>'反馈管理','icon'=>'icon-tasks','class'=>'','url'=>'Feedback/index','is_developer'=>0),
            ),
            array(
                'group' => array('title'=>'文章管理','icon'=>'icon-file','class'=>'','url'=>'Article/index','is_developer'=>0),
            ),
            array(
                'group' => array('title'=>'退货原因','icon'=>'icon-exclamation-sign','class'=>'','url'=>'Reason/index','is_developer'=>0),
            ),
            array(
                'group' => array('title'=>'新闻管理','icon'=>' icon-list','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'新闻类别','url'=>'NewsType/index','class'=>'','is_developer'=>0),
                    array('title'=>'新闻列表','url'=>'News/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title'=>'信息管理','icon'=>'icon-envelope','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
//                    array('title'=>'短信模板设置','url'=>'SendTemplate/index','class'=>'','is_developer'=>0),
//                    array('title'=>'短信发送记录','url'=>'SendLog/index','class'=>'','is_developer'=>0),
                    array('title'=>'系统消息发布','url'=>'Message/index','class'=>'','is_developer'=>0),
                )
            ),
//            array(
//                'group' => array('title'=>'管理员操作','icon'=>'icon-user','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
//                '_child' => array(
//                    array('title'=>'管理员信息','url'=>'Administrator/index','class'=>'','is_developer'=>0),
//                    array('title'=>'分组权限','url'=>'AuthGroup/index','class'=>'','is_developer'=>0),
//                    array('title'=>'行为信息','url'=>'Action/index','class'=>'','is_developer'=>0),
//                    array('title'=>'行为日志','url'=>'ActionLog/index','class'=>'','is_developer'=>0),
//                )
//            ),

//            array(
//                'group' => array('title'=>'数据管理','icon'=>'icon-tasks','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
//                '_child' => array(
//                    array('title'=>'数据备份','url'=>'Export/index','class'=>'','is_developer'=>0),
//                    array('title'=>'数据还原','url'=>'Import/index','class'=>'','is_developer'=>0)
//                )
//            ),
            array(
                'group' => array('title'=>'抽奖管理','icon'=>'icon-sitemap','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
//                    array('title'=>'短信模板设置','url'=>'SendTemplate/index','class'=>'','is_developer'=>0),
//                    array('title'=>'短信发送记录','url'=>'SendLog/index','class'=>'','is_developer'=>0),
                    array('title'=>'奖品列表','url'=>'Lottery/index','class'=>'','is_developer'=>0),
                    array('title'=>'中奖订单','url'=>'LotteryOrder/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title'=>'积分管理','icon'=>' icon-star','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
//                    array('title'=>'短信模板设置','url'=>'SendTemplate/index','class'=>'','is_developer'=>0),
//                    array('title'=>'短信发送记录','url'=>'SendLog/index','class'=>'','is_developer'=>0),
                    array('title'=>'签到积分','url'=>'Sign/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title'=>'银行卡管理','icon'=>'icon-list-alt','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
//                    array('title'=>'短信模板设置','url'=>'SendTemplate/index','class'=>'','is_developer'=>0),
//                    array('title'=>'短信发送记录','url'=>'SendLog/index','class'=>'','is_developer'=>0),
                    array('title'=>'支持银行','url'=>'SupportBank/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title'=>'快递公司管理','icon'=>'icon-truck','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
//                    array('title'=>'短信模板设置','url'=>'SendTemplate/index','class'=>'','is_developer'=>0),
//                    array('title'=>'短信发送记录','url'=>'SendLog/index','class'=>'','is_developer'=>0),
                    array('title'=>'快递公司','url'=>'DeliveryCompany/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title'=>'系统设置','icon'=>'icon-wrench','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'网站设置','url'=>'ConfigSet/index?config_group=1','class'=>'','is_developer'=>0),
//                    array('title'=>'参数设置','url'=>'Remark/index','class'=>'','is_developer'=>0),
//                    array('title'=>'配置管理','url'=>'Config/index','class'=>'','is_developer'=>0),
//                    array('title'=>'启动页管理','url'=>'OpenPage/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title'=>'扩展管理','icon'=>'icon-hdd','class'=>'','url'=>'javascript:void(0);','is_developer'=>1),
                '_child' => array(
                    array('title'=>'插件管理','url'=>'Plugins/index','class'=>'','is_developer'=>1),
                    array('title'=>'钩子管理','url'=>'Hooks/index','class'=>'','is_developer'=>1)
                )
            ),
        ),
    );