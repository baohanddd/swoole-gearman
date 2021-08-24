<?php

use baohan\SwooleGearman\Collection;
use baohan\SwooleGearman\Server;
use Monolog\Logger;

require('vendor/autoload.php');

try {
    $s = new \baohan\SwooleGearman\Gearman(Logger::INFO);
    $s->worker_num = 10;
    $s->host = 'gearman';
    $s->port = '4730';
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