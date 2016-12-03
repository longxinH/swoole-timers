<?php

require 'SinglePHP.class.php';
include '../vendor/autoload.php';

define("ROOT_PATH", realpath(dirname(__FILE__)));
define('APP_PATH',  ROOT_PATH . '/App');
define('CONFIG_PATH',  APP_PATH . '/Config');

$config = [];
foreach (glob(CONFIG_PATH . '/*.ini') as $val) {
    $config = array_merge($config, parse_ini_file($val, true));
}

//error_reporting(E_ALL);
//ini_set('display_errors', 0);

SinglePHP::getInstance($config)->run();