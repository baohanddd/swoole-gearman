<?php
require('vendor/autoload.php');
define('APP_PATH', realpath('.'));

use baohan\SwooleGearman\Collection;
use baohan\SwooleGearman\Exception\ContextException;
use baohan\SwooleGearman\Queue\MnsQueue;
use baohan\SwooleGearman\Queue\Worker;
use baohan\SwooleGearman\Router;
use baohan\SwooleGearman\Server;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

use AliyunMNS\Client;
use AliyunMNS\Requests\SendMessageRequest;
use AliyunMNS\Requests\CreateQueueRequest;
use AliyunMNS\Exception\MnsException;

spl_autoload_register(function($class) {
    static $ds = '/';

    $_route_map = ['App\Job' => APP_PATH . '/app/jobs/'];

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

$log = new Logger('worker');
$log->pushHandler(new StreamHandler('/data/logs/worker.log', Logger::DEBUG));
$log->pushHandler(new StreamHandler('php://stdout', Logger::DEBUG));

$endPoint  = "<ali-mns-end-point>";
$accessId  = "<your-access-id>";
$accessKey = "<your-access-key>";
$queueName = "<your-queue-name>";

try {
    $port = 6379;
    $client = new Client($endPoint, $accessId, $accessKey);
    /**
     * @var \AliyunMNS\Queue $mns
     */
    $mns = $client->getQueueRef($queueName);
    $queue = new MnsQueue($mns);
    $w = new Worker($queue, $log);

    $router = new Router();
    $router->setListenQueueName('worker_queue');
    $router->addCallback('fu::timestamp::save', function () use ($log) {
        $class = "App\Job\Timestamp\Saver";
        return new $class($log);
    });

    $w->addRouter($router);

    $s = new Server($w);
    $s->setWorkerNum(3);
    $s->setReactorNum(1);
    $s->setSwoolePort(9500);
    $s->start();
} catch (ContextException $e) {
    $log->err($e->getMessage(), [$e->getCode(), $e->getContext()]);
}