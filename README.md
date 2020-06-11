Swoole-gearman
====

A multi-processes worker framework based on Swoole and (Redis, Ali MNS)

Install
====

Install `swoole` and `redis` first.

```
$ composer require baohan/swoole-gearman
```

How
====

Quick start

using `redis` as queue

```php
use baohan\SwooleGearman\Exception\ContextException;
use baohan\SwooleGearman\Queue\RedisQueue;
use baohan\SwooleGearman\Queue\Worker;
use baohan\SwooleGearman\Router;
use baohan\SwooleGearman\Server;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require('vendor/autoload.php');
define('APP_PATH', realpath('.'));

$log = new Logger('worker');
$log->pushHandler(new StreamHandler('/data/logs/worker.log', Logger::DEBUG));
$log->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

try {
    $host = '172.17.0.1';
    $port = 6379;
    $redis = new \Redis();
    $redis->connect($host, $port);
    $queue = new RedisQueue($redis);
    $w = new Worker($queue, $log);

    $router = new Router();
    $router->setListenQueueName('worker_queue');
    $router->addCallback('fu::timestamp::save', function () use ($log) {
        $class = "App\Job\Timestamp\Saver";
        return new $class($log);
    });

    $w->addRouter($router);

    $s = new Server($w);
    // set number of worker process
    $s->setWorkerNum(2);
    $s->setReactorNum(1);
    $s->setSwoolePort(9500);
    $s->start();
} catch (ContextException $e) {
    $log->err($e->getMessage(), [$e->getCode(), $e->getContext()]);
}

```

That's all.

