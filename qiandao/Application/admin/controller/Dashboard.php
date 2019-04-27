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

        $countData = [
            'todayUsersubmitexamine' => 22,
            'totalUsersubmitexamine' => 33,
            'todayReceiveorder' => 44,
            'totalReceiveorder' => 55,
            'todayDynamics' => 66,
            'totalDynamics' => 77,
            'todayMessages' => 88,
            'totalMessages' => 99,
        ];;

        return $countData;
    }

}