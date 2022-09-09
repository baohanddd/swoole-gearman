<?php

use baohan\SwooleGearman\Collection;
use Monolog\Logger;

require('vendor/autoload.php');

try {
    $s = new \baohan\SwooleGearman\Redis(Logger::DEBUG);
    $s->worker_num = 3;
    $s->host = 'redis';
    $s->port = 6379;
    $s->auth = 'futureLinkDev:Redis2021';
    $s->key  = 'jobs';
    $s->addCallback('timestamp::print', function () {
        return new class() extends \baohan\SwooleGearman\Job {
            public function execute(Collection $payload, int $workerId): bool {
//                usleep(50000);
                echo "worker[{$workerId}] => ".$payload['message'].PHP_EOL;
                return true;
            }
        };
    });
    $s->start();
} catch (Throwable $e) {
    echo $e->getMessage();
}