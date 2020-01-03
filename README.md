Swoole-gearman
====

A multi-processes worker framework based on Swoole and [Gearman|Redis]

Install
====

Install `swoole` and `redis` first.

```
$ composer require baohan/swoole-gearman
```

How
====

Quick start

```php

require('vendor/autoload.php');
define('APP_PATH', realpath('.'));

$log = new Logger('worker');
$log->pushHandler(new StreamHandler('/data/logs/worker.log', Logger::DEBUG));
$log->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

try {
    $port = 6379;
    $w = new Worker('redis', $port, $log);

    $router = new Router();
    $router->setListenQueueName('worker_queue');
    $router->addCallback('fu::timestamp::save', function () use ($log) {
        $class = "App\Job\Timestamp\Saver";
        return new $class($log);
    });

    $w->addRouter($router);

    $s = new Server($w);
    $s->setWorkerNum(2);
    $s->setReactorNum(1);
    $s->setSwoolePort(9500);
    $s->start();
} catch (ContextException $e) {
    $log->err($e->getMessage(), [$e->getCode(), $e->getContext()]);
}

```

That's all.

