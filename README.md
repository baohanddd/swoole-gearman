# swoole-gearman
A multi-processes worker framework based on Swoole and Gearman

Install
====

Install `swoole` and `gearman` first.


How
====

Quick start

```php

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

```

Configure
====

Event callbacks

