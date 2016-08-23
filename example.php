<?php
define('APP_PATH', realpath('.'));
define('DS', "::");

spl_autoload_register(function($class) {
    static $ds = '/';

    $_route_map = ['baohan\SwooleGearman' => APP_PATH . '/src/'];

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
    $worker->addCallback('user::created');
    $worker->addCallback('user::updated');

    $router = new \baohan\SwooleGearman\Router();
    $router->setPrefix("\\App\\Job\\");
    $router->setExecutor("execute");
    $router->setDecode(function($payload) {
        return \json_decode($payload, true);
    });
    $worker->addRouter($router);
    
    $serv = new \baohan\SwooleGearman\Server($worker);
    $serv->setSwoolePort(9505);
    // custom callback event
    $serv->setEvtStart(function($serv) {
        echo "server start!" . PHP_EOL;
    });
    $serv->start();
} catch(\Exception $e) {
    echo "Caught Exception: " . $e->getMessage() . PHP_EOL;
}
