<?php
define('APP_PATH', realpath('.'));
define('DS', "::");

$_route_map = [
    "baohan\SwooleGearman"  => APP_PATH . "/",
];

spl_autoload_register(function($class) use ($_route_map) {
    static $ds = '/';

    $parts  = explode('\\', $class);
    $app    = array_shift($parts);
    $module = array_shift($parts);
    if(!isset($_route_map[$app."\\".$module])) {
        echo 'Can not found file '.$class;
        exit();
    }
    $path = $_route_map[$app."\\".$module] . str_replace('\\', $ds, implode('\\', $parts)) . '.php';
    include $path;
});

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
