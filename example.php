<?php
define('APP_PATH', realpath('.'));
define('DS', "::");

include APP_PATH . "/vendor/autoload.php";

$GLOBALS['cfg'] = include(APP_PATH . "/config/config.php");

try {
    $serv = new \baohan\SwooleGearman\Server();

    $serv->setEvtStart(function($serv) {
        echo "server start!" . PHP_EOL;
    });

    $serv->addCallback('user::created', function ($payload) {
        // doing something...
    });
    $serv->addCallback('user::updated', "\\App\\Job\\User::update");
    $serv->start();
} catch(\Exception $e) {
    echo "Caught Exception: " . $e->getMessage() . PHP_EOL;
}
