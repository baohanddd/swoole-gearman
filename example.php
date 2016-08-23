<?php
define('APP_PATH', realpath('.'));
define('DS', "::");

try {
    $worker = new \baohan\SwooleGearman\Queue\Worker();
    $worker->addCallback('user::created', function ($payload) {
        // doing something...
    });
    $worker->addCallback('user::updated', "\\App\\Job\\User::update");
    // custom handle $payload
    $worker->setDecode(function($payload) {
        return new Document($payload);
    });
    
    $serv = new \baohan\SwooleGearman\Server($worker);
    // custom callback event
    $serv->setEvtStart(function($serv) {
        echo "server start!" . PHP_EOL;
    });
    $serv->start();
} catch(\Exception $e) {
    echo "Caught Exception: " . $e->getMessage() . PHP_EOL;
}
