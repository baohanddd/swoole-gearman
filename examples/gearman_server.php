<?php

use baohan\SwooleGearman\Collection;
use Monolog\Logger;

require('vendor/autoload.php');

try {
    $s = new \baohan\SwooleGearman\Gearman(Logger::DEBUG);
    $s->worker_num = 3;
    $s->host = 'gearman';
    $s->port = '4730';
    $s->addCallback('timestamp::print', function () {
        return new class() extends \baohan\SwooleGearman\Job {
            public function execute(Collection $payload, int $workerId): bool {
                usleep(50000);
                echo "worker[{$workerId}] => ".$payload['message'].PHP_EOL;
                return true;
            }
        };
    });
    $s->start();
} catch (Throwable $e) {
    echo $e->getMessage();
}