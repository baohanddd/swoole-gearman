# swoole-gearman
A multi-processes worker framework based on Swoole and Gearman

Install
====

Install `swoole` and `redis` first.


How
====

Quick start

```php

$log = new Logger('worker');
$log->pushHandler(new StreamHandler('/data/logs/worker.log', Logger::DEBUG));
$log->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$port = 6379;
$w = new Worker('redis', $port, $log);

$router = new Router($log);
$router->setPrefix("\\App\\Job\\");
$router->setExecutor("execute");
$router->setListenQueueName('worker_queue');
$router->setDecode(function($payload) {
    return new Collection($payload);
});

$w->addRouter($router);

$s = new Server($w);
$s->setWorkerNum(2);
$s->setReactorNum(1);
$s->setSwoolePort(9500);
$s->start();

```

Configure
====

Event callbacks

