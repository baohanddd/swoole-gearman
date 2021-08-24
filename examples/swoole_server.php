<?php

use baohan\SwooleGearman\Collection;
use baohan\SwooleGearman\Server;
use Monolog\Logger;

require('vendor/autoload.php');

try {
    $s = new Server(Logger::INFO);
    $s->worker_num = 1;
    $s->task_worker_num = 5;
    $s->addCallback('timestamp::print', function () {
        return new class() extends \baohan\SwooleGearman\Job {
            public function execute(Collection $payload, int $workerId): bool {
                sleep(1);
                echo "worker[{$workerId}] => ".$payload['message'].PHP_EOL;
                return true;
            }
        };
    });
    $s->start();
} catch (Throwable $e) {
    echo $e->getMessage();
}