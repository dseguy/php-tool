<?php

require_once 'Zend/Controller/Action.php';

class TestController extends Zend_Controller_Action {

    public function init() {
        $this->initView();
        $this->_helper->layout->setLayout('bootstrap');
    }

    public function noLayout() {
        $this->_helper->layout->disableLayout();
    }

    public function indexAction() {
        $this->view->headTitle('接口管理', Zend_View_Helper_Placeholder_Container_Abstract::PREPEND);
        $interFaces = array(
            array(
                'id'=>'i_gameinfo',
                'label'=>'游戏信息',
                'items'=>array(
                    array(
                        'label'=>'1.1. 渠道列表',
                        'url'=>'/select/fetchselectpartner',
                        'data'=>'{productID:1}',
                    ),
                    array(
                        'label'=>'1.2. 区服列表',
                        'url'=>'/select/fetchselectgameserver',
                        'data'=>'{productID:1}',
                    ),
                ),
            ),
            array(
                'id'=>'i_youxiwanjia',           //tab标签的id
                'label'=>'游戏玩家',        //tab标签显示的内容
                'items'=>array(
                    array(
                        'id'=>'i_xinzengwanjia',           //tab标签的id
                        'label'=>'新增玩家',        //tab标签显示的内容
                        'items'=>array(             //tab标签下的接口列表
                            array(
                                'label'=>'1.1. 新增激活和账户',
                                'url'=>'/newplayer/fetchinstallnewplayerlist',
                                'data'=>'{startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.2. 玩家转化',
                                'url'=>'/newplayer/conversionlist',
                                'data'=>'{startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.3. 单设备账户数量分析',
                                'url'=>'/newplayer/subaccount',
                                'data'=>'{startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.4. 玩家账户类型',
                                'url'=>'/newplayer/newplayerlist',
                                'data'=>'{groupBy:"accountType",startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.5. 新玩家全球地区',
                                'url'=>'/newplayer/newplayerlist',
                                'data'=>'{groupBy:"country",startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.6. 新玩家地区(中国)',
                                'url'=>'/newplayer/newplayerlist',
                                'data'=>'{groupBy:"location",startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.7. 新玩家渠道',
                                'url'=>'/newplayer/newplayerlist',
                                'data'=>'{groupBy:"partner",startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.8. 新玩家性别',
                                'url'=>'/newplayer/newplayerlist',
                                'data'=>'{groupBy:"sex",startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.9. 新玩家年龄',
                                'url'=>'/newplayer/newplayerlist',
                                'data'=>'{groupBy:"age",startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                        ),
                    ),
                    array(
                        'id'=>'i_huoyuewanjia',
                        'label'=>'活跃玩家',
                        'items'=>array(
                            array(
                                'label'=>'1.1. DAU',
                                'url'=>'/activity/newandolddaulist',
                                'data'=>'{startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.2. WAU',
                                'url'=>'/activity/waulist',
                                'data'=>'{startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.3. MAU',
                                'url'=>'/activity/maulist',
                                'data'=>'{startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.4. DAU/MAU',
                                'url'=>'/activity/daudividemaulist',
                                'data'=>'{startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.5. 活跃玩家 已玩天数',
                                'url'=>'/activity/groupedplayer',
                                'data'=>'{groupBy:"use_day_type",startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.6. 活跃玩家 等级',
                                'url'=>'/activity/groupedplayer',
                                'data'=>'{groupBy:"level",startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.7. 活跃玩家全球地区',
                                'url'=>'/activity/groupedplayer',
                                'data'=>'{groupBy:"country",startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.8. 活跃玩家中国地区',
                                'url'=>'/activity/groupedplayer',
                                'data'=>'{groupBy:"location",startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.9. 活跃玩家渠道',
                                'url'=>'/activity/groupedplayer',
                                'data'=>'{groupBy:"partner",startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.10. 活跃玩家性别',
                                'url'=>'/activity/groupedplayer',
                                'data'=>'{groupBy:"sex",startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.11. 活跃玩家年龄',
                                'url'=>'/activity/groupedplayer',
                                'data'=>'{groupBy:"age",startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                        ),
                    ),
                    array(
                        'id'=>'i_wanjialiucun',
                        'label'=>'玩家留存',
                        'items'=>array(
                            array(
                                'label'=>'1.1. 新增账户留存',
                                'url'=>'/retention/retentionlist',
                                'data'=>'{"retentionBean":{"type":"newPlayerRetention","keepDay":1},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.2. 激活设备留存',
                                'url'=>'/retention/retentionlist',
                                'data'=>'{"retentionBean":{"type":"newDeviceRetention","keepDay":1},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.3. 留存用户(新增日等级)',
                                'url'=>'/retention/retentionuseranalys',
                                'data'=>'{groupBy:"firstDayLevel","retentionBean":{"type":"newPlayerRetention","keepDay":1},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.4. 留存用户(新增日游戏次数)',
                                'url'=>'/retention/retentionuseranalys',
                                'data'=>'{groupBy:"logintimes","retentionBean":{"type":"newPlayerRetention","keepDay":1},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.5. 留存用户(新增日游戏时长)',
                                'url'=>'/retention/retentionuseranalys',
                                'data'=>'{groupBy:"firstDayUseTime","retentionBean":{"type":"newPlayerRetention","keepDay":1},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.6. 留存用户(新增日是否付费)',
                                'url'=>'/retention/retentionuseranalys',
                                'data'=>'{groupBy:"isFirstDayCharge","retentionBean":{"type":"newPlayerRetention","keepDay":1},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.7. 留存用户(新玩家性别)',
                                'url'=>'/retention/retentionuseranalys',
                                'data'=>'{groupBy:"sex","retentionBean":{"type":"newPlayerRetention","keepDay":1},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.8. 留存用户(新玩家年龄)',
                                'url'=>'/retention/retentionuseranalys',
                                'data'=>'{groupBy:"age","retentionBean":{"type":"newPlayerRetention","keepDay":1},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.9. 自定义留存',
                                'url'=>'/customretention/customretentionlist',
                                'data'=>'{"retentionBean":{"type":"newPlayerRetention","minLoginTimes":1,"ayalyseParticle":"day","keepDay":1},"curPageNum":1,"pageSize":9,startTime:140806,endTime:140817,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                        ),
                    ),
                    array(
                        'id'=>'i_fufeizhuanhua',
                        'label'=>'付费转化',
                        'items'=>array(
                            array(
                                'label'=>'1.1. test',
                                'url'=>'/churned/fetchperdaylostnumandrate',
                                'data'=>'{"churnedParameterBean":{"playerType":3,"dayType":7,"analysType":1},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                        ),
                    ),
                    array(
                        'id'=>'i_wanjialiushi',
                        'label'=>'玩家流失',
                        'items'=>array(
                            array(
                                'label'=>'1.1. 每日流失',
                                'url'=>'/churned/fetchperdaylostnumandrate',
                                'data'=>'{"churnedParameterBean":{"playerType":3,"dayType":7,"analysType":1},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.2. 流失用户分析',
                                'url'=>'/churned/lostuseranalysnumlist',
                                'data'=>'{"churnedParameterBean":{"playerType":3,"dayType":7,"analysType":1},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.3. 每日回流',
                                'url'=>'/churned/perdayreturnnumlist',
                                'data'=>'{"churnedParameterBean":{"playerType":3,"dayType":7,"analysType":4},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                        ),
                    ),
                    array(
                        'id'=>'i_youxixiguan',
                        'label'=>'游戏习惯',
                        'items'=>array(
                            array(
                                'label'=>'1.1. 平均游戏时长与次数',
                                'url'=>'/behavior/avggametimeslist',
                                'data'=>'{"displayDateTip":"-7","productID":"1","platformID":"","partnerID":"348,352,358","gameserverID":"345,350,355","startTime":"140805","endTime":"140821","player":{"userType":"newPlayer","dayType":"day"}}',
                            ),
                            array(
                                'label'=>'1.2. 游戏频次',
                                'url'=>'/behavior/gamefrequencylist',
                                'data'=>'{"displayDateTip":"-7","productID":"1","platformID":"","partnerID":"348,352,358","gameserverID":"345,350,355","startTime":"140805","endTime":"140821","player":{"userType":"newPlayer","groupType":"weekGameDays"}}',
                            ),
                            array(
                                'label'=>'1.3. 游戏时长',
                                'url'=>'/behavior/avggamedurationlist',
                                'data'=>'{"displayDateTip":"-7","productID":"1","platformID":"","partnerID":"348,352,358","gameserverID":"345,350,355","startTime":"140805","endTime":"140821","player":{"userType":"newPlayer","groupType":"dayUseTime"}}',
                            ),
                            array(
                                'label'=>'1.4. 游戏时段',
                                'url'=>'/behavior/gametimeslist',
                                'data'=>'{"displayDateTip":"-7","productID":"1","platformID":"","partnerID":"348,352,358","gameserverID":"345,350,355","startTime":"140805","endTime":"140821","player":{"userType":"newPlayer","groupType":"hour"}}',
                            ),
                        ),
                    ),
                    array(
                        'id'=>'i_shebeixiangguan',
                        'label'=>'设备相关',
                        'items'=>array(
                            array(
                                'label'=>'1.1. 机型',
                                'url'=>'/activity/groupedplayer',
                                'data'=>'{"groupBy":"mobile","displayDateTip":"99","productID":"1","platformID":"","partnerID":"348,352,358","gameserverID":"345,350,355","startTime":"140805","endTime":"140821","player":{"userType":"newPlayer"}}',
                            ),
                            array(
                                'label'=>'1.2. 分辨率',
                                'url'=>'/activity/groupedplayer',
                                'data'=>'{"groupBy":"pixel","displayDateTip":"99","productID":"1","platformID":"","partnerID":"348,352,358","gameserverID":"345,350,355","startTime":"140805","endTime":"140821","player":{"userType":"newPlayer"}}',
                            ),
                            array(
                                'label'=>'1.3. 操作系统',
                                'url'=>'/activity/groupedplayer',
                                'data'=>'{"groupBy":"mobileos","displayDateTip":"99","productID":"1","platformID":"","partnerID":"348,352,358","gameserverID":"345,350,355","startTime":"140805","endTime":"140821","player":{"userType":"newPlayer"}}',
                            ),
                            array(
                                'label'=>'1.4. 联网方式分布',
                                'url'=>'/activity/groupedplayer',
                                'data'=>'{"groupBy":"network","displayDateTip":"99","productID":"1","platformID":"","partnerID":"348,352,358","gameserverID":"345,350,355","startTime":"140805","endTime":"140821","player":{"userType":"newPlayer"}}',
                            ),
                            array(
                                'label'=>'1.5. 运营商',
                                'url'=>'/activity/groupedplayer',
                                'data'=>'{"groupBy":"carrier","displayDateTip":"99","productID":"1","platformID":"","partnerID":"348,352,358","gameserverID":"345,350,355","startTime":"140805","endTime":"140821","player":{"userType":"newPlayer"}}',
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'id'=>'i_charge',           //tab标签的id
                'label'=>'收入分析',        //tab标签显示的内容
                'items'=>array(
                    array(
                        'id'=>'i_incomeNum',           //tab标签的id
                        'label'=>'收入数据',        //tab标签显示的内容
                        'items'=>array(             //tab标签下的接口列表
                            array(
                                'label'=>'1.1. 收入金额',
                                'url'=>'/incomedata/incomenumlist',
                                'data'=>'{startTime:140806,endTime:140806,partnerID:"349",gameserverID:"362",productID:1}',
                            ),
                            array(
                                'label'=>'1.2.充值次数',
                                'url'=>'/incomedata/chargetimeslist',
                                'data'=>'{startTime:140805,endTime:140821,partnerID:"349",gameserverID:"362",productID:1}',
                            ),
                            array(
                                'label'=>'1.3. 充值人数',
                                'url'=>'/incomedata/chargeplayernumlist',
                                'data'=>'{startTime:140805,endTime:140821,partnerID:"349",gameserverID:"362",productID:1}',
                            ),
                            array(
                                'label'=>'1.4. 等级收入金额',
                                'url'=>'/incomedata/levelincomenumdistribution',
                                'data'=>'{groupBy:"accountType",startTime:140805,endTime:140821,partnerID:"349",gameserverID:"362",productID:1}',
                            ),
                            array(
                                'label'=>'1.5. 等级充值人次',
                                'url'=>'/incomedata/levelchargeplayernumdistribution',
                                'data'=>'{groupBy:"country",startTime:140805,endTime:140821,partnerID:"349",gameserverID:"362",productID:1}',
                            ),
                            array(
                                'label'=>'1.6. 各渠道收入',
                                'url'=>'/incomedata/partnerincomedistribution',
                                'data'=>'{groupBy:"location",startTime:140805,endTime:140821,partnerID:"349",gameserverID:"362",productID:1}',
                            ),
                        ),
                    ),
                    array(
                        'id'=>'i_payPermeate',
                        'label'=>'付费渗透',
                        'items'=>array(
                            array(
                                'label'=>'1.1. 日付费率',
                                'url'=>'/payPermeate/dayPayRateList',
                                'data'=>'{startTime:140805,endTime:140821,partnerID:"349",gameserverID:"362",productID:1}',
                            ),
                            array(
                                'label'=>'1.2. 周付费率',
                                'url'=>'/payPermeate/weekPayRateList',
                                'data'=>'{startTime:140805,endTime:140821,partnerID:"349",gameserverID:"362",productID:1}',
                            ),
                            array(
                                'label'=>'1.3. 月付费率',
                                'url'=>'/payPermeate/monthPayRateList',
                                'data'=>'{startTime:140805,endTime:140821,partnerID:"349",gameserverID:"362",productID:1}',
                            ),
                            array(
                                'label'=>'1.4. ARPU（日）',
                                'url'=>'/payPermeate/dayARPUList',
                                'data'=>'{startTime:140805,endTime:140821,partnerID:"349",gameserverID:"362",productID:1}',
                            ),
                            array(
                                'label'=>'1.5. ARPU（月）',
                                'url'=>'/payPermeate/monthARPUList',
                                'data'=>'{startTime:140805,endTime:140821,partnerID:"349",gameserverID:"362",productID:1}',
                            ),
                            array(
                                'label'=>'1.6. ARPPU（日）',
                                'url'=>'/payPermeate/dayARPPUList',
                                'data'=>'{startTime:140805,endTime:140821,partnerID:"349",gameserverID:"362",productID:1}',
                            ),
                            array(
                                'label'=>'1.7. ARPPU（月）',
                                'url'=>'/payPermeate/monthARPPUList',
                                'data'=>'{startTime:140805,endTime:140821,partnerID:"3498",gameserverID:"362",productID:1}',
                            ),
                            array(
                                'label'=>'1.8. 渠道日付费率',
                                'url'=>'/activity/fetchDailyRateOfPay',
                                'data'=>'{startTime:140805,endTime:140821,partnerID:"349",gameserverID:"3625",productID:1}',
                            ),
                            array(
                                'label'=>'1.9. 渠道日ARPU',
                                'url'=>'/activity/fetchDailyARPU',
                                'data'=>'{startTime:140805,endTime:140821,partnerID:"349",gameserverID:"362",productID:1}',
                            ),
                            array(
                                'label'=>'1.10. 渠道ARPPU',
                                'url'=>'/activity/fetchDailyARPPU',
                                'data'=>'{startTime:140805,endTime:140821,partnerID:"349",gameserverID:"362",productID:1}',
                            ),
                           
                        ),
                    ),
                    array(
                        'id'=>'i_wanjialiucun',
                        'label'=>'玩家留存',
                        'items'=>array(
                            array(
                                'label'=>'1.1. 新增账户留存',
                                'url'=>'/retention/retentionlist',
                                'data'=>'{"retentionBean":{"type":"newPlayerRetention","keepDay":1},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.2. 激活设备留存',
                                'url'=>'/retention/retentionlist',
                                'data'=>'{"retentionBean":{"type":"newDeviceRetention","keepDay":1},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.3. 留存用户(新增日等级)',
                                'url'=>'/retention/retentionuseranalys',
                                'data'=>'{groupBy:"firstDayLevel","retentionBean":{"type":"newPlayerRetention","keepDay":1},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.4. 留存用户(新增日游戏次数)',
                                'url'=>'/retention/retentionuseranalys',
                                'data'=>'{groupBy:"logintimes","retentionBean":{"type":"newPlayerRetention","keepDay":1},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.5. 留存用户(新增日游戏时长)',
                                'url'=>'/retention/retentionuseranalys',
                                'data'=>'{groupBy:"firstDayUseTime","retentionBean":{"type":"newPlayerRetention","keepDay":1},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.6. 留存用户(新增日是否付费)',
                                'url'=>'/retention/retentionuseranalys',
                                'data'=>'{groupBy:"isFirstDayCharge","retentionBean":{"type":"newPlayerRetention","keepDay":1},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.7. 留存用户(新玩家性别)',
                                'url'=>'/retention/retentionuseranalys',
                                'data'=>'{groupBy:"sex","retentionBean":{"type":"newPlayerRetention","keepDay":1},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.8. 留存用户(新玩家年龄)',
                                'url'=>'/retention/retentionuseranalys',
                                'data'=>'{groupBy:"age","retentionBean":{"type":"newPlayerRetention","keepDay":1},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.9. 自定义留存',
                                'url'=>'/customretention/customretentionlist',
                                'data'=>'{"retentionBean":{"type":"newPlayerRetention","minLoginTimes":1,"ayalyseParticle":"day","keepDay":1},"curPageNum":1,"pageSize":9,startTime:140806,endTime:140817,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                        ),
                    ),
                    array(
                        'id'=>'i_fufeizhuanhua',
                        'label'=>'付费转化',
                        'items'=>array(
                            array(
                                'label'=>'1.1. test',
                                'url'=>'/churned/fetchperdaylostnumandrate',
                                'data'=>'{"churnedParameterBean":{"playerType":3,"dayType":7,"analysType":1},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                        ),
                    ),
                    array(
                        'id'=>'i_wanjialiushi',
                        'label'=>'玩家流失',
                        'items'=>array(
                            array(
                                'label'=>'1.1. 每日流失',
                                'url'=>'/churned/fetchperdaylostnumandrate',
                                'data'=>'{"churnedParameterBean":{"playerType":3,"dayType":7,"analysType":1},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.2. 流失用户分析',
                                'url'=>'/churned/lostuseranalysnumlist',
                                'data'=>'{"churnedParameterBean":{"playerType":3,"dayType":7,"analysType":1},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                            array(
                                'label'=>'1.3. 每日回流',
                                'url'=>'/churned/perdayreturnnumlist',
                                'data'=>'{"churnedParameterBean":{"playerType":3,"dayType":7,"analysType":4},startTime:140805,endTime:140821,partnerID:"348,352,358",gameserverID:"345,350,355",productID:1}',
                            ),
                        ),
                    ),
                ),
            ),
        );
        $this->view->interFaces = $interFaces;
    }

}
