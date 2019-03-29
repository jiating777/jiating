<?php

namespace app\admin\controller;

use think\Request;

class Presale extends Product
{

    public function _initialize()
    {
        parent::_initialize();

        $this->model = 'Product';

        $defaultWhere = $this->getDefaultWhere();
        $defaultWhere['style'] = 2;
        $this->defaultWhere = $defaultWhere;
    }

}