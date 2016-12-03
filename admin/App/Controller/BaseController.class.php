<?php

class BaseController extends Controller {

    protected function _init()
    {
        $this->assign('action', isset($_GET['a']) ? $_GET['a'] : '');
    }

} 
