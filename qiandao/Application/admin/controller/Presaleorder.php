<?php

namespace app\admin\controller;

use think\Request;

class Presaleorder extends Order
{

    public function _initialize()
    {
        parent::_initialize();

        $this->model = 'Order';
        $this->defaultWhere = [
            'style' => 2
        ];
    }

}