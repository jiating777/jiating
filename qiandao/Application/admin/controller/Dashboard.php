<?php

namespace app\admin\controller;

class Dashboard extends Base
{

    public function index(){
        $countData = $this->getCountData();

        $this->assign('countData', $countData);
        $title = '签到2019';
        $this->assign('title', $title);

        return $this->view->fetch('index');
    }

    /**
     * 今日&当月 统计数据
     */
    public function getCountData(){
        return [
            'todayUsersubmitexamine' => 22,
            'totalUsersubmitexamine' => 33,
            'todayReceiveorder' => 44,
            'totalReceiveorder' => 55,
            'todayDynamics' => 66,
            'totalDynamics' => 77,
            'todayMessages' => 88,
            'totalMessages' => 99,
        ];
        $startDay = strtotime(date('Y-m-d'), time());
        //$startMonth = strtotime(date("Y-m-01"));
        //$endMonth = strtotime(date("Y-m-01 23:59:59")." +1 month -1 day");

        // TODO 条件是什么？镇/村/..
        // 今日待认证村民
        $usersubmitexamineModel = model('Usersubmitexamine');
        $usersubmitexamineDayWhere = [
            'createDate' => ['between time', [$startDay, $startDay+86400]]
        ];
        $todayUsersubmitexamine = $usersubmitexamineModel->where($usersubmitexamineDayWhere)->count();
        // 累计已认证村民人数
        $totalUsersubmitexamine = $usersubmitexamineModel->where([])->count();

        // 今日申请商品人数
        $receiveorderModel = model('Order');
        $receiveorderDayWhere = [
            'style' => 3,
            'createDate' => ['between time', [$startDay, $startDay+86400]]
        ];
        $todayReceiveorder = $receiveorderModel->where($receiveorderDayWhere)->count();
        // 累计申请样品人数
        $totalReceiveorder = $receiveorderModel->where(['style' => 3])->count();

        // 今日新增动态
        $dynamicsModel = model('Communitydynamics');
        $dynamicsDayWhere = [
            'createDate' => ['between time', [$startDay, $startDay+86400]]
        ];
        $todayDynamics = $dynamicsModel->where($dynamicsDayWhere)->count();
        // 累计发布动态数
        $totalDynamics = $dynamicsModel->where([])->count();

        // 今日新增留言数
        $messagesModel = model('Messages');
        $messagesDayWhere = [
            'createDate' => ['between time', [$startDay, $startDay+86400]]
        ];
        $todayMessages = $messagesModel->where($messagesDayWhere)->count();
        // 累计新增留言数
        $totalMessages = $messagesModel->where([])->count();

        $countData = [
            'todayUsersubmitexamine' => $todayUsersubmitexamine,
            'totalUsersubmitexamine' => $totalUsersubmitexamine,
            'todayReceiveorder' => $todayReceiveorder,
            'totalReceiveorder' => $totalReceiveorder,
            'todayDynamics' => $todayDynamics,
            'totalDynamics' => $totalDynamics,
            'todayMessages' => $todayMessages,
            'totalMessages' => $totalMessages,
        ];

        return $countData;
    }

}