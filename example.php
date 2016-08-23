<?php
define('APP_PATH', realpath('.'));
define('DS', "::");

spl_autoload_register(['baohan\\SwooleGearman\\' => array(APP_PATH . '/src')]);


try {
    $worker = new \baohan\SwooleGearman\Queue\Worker();
    $worker->addCallback('user::created');
    $worker->addCallback('user::updated');

    $router = new \baohan\SwooleGearman\Router();
    $router->setPrefix("\\App\\Job\\");
    $router->setExecutor("execute");
    $router->setDecode(function($payload) {
        return new Document($payload);
    });
    $worker->addRouter($router);
    
    $serv = new \baohan\SwooleGearman\Server($worker);
    // custom callback event
    $serv->setEvtStart(function($serv) {
        echo "server start!" . PHP_EOL;
    });
    $serv->start();
} catch(\Exception $e) {
    echo "Caught Exception: " . $e->getMessage() . PHP_EOL;
}
