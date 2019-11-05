<?php

$r = new Redis();
$r->connect('redis', 6379);

echo 'connected redis...'.PHP_EOL;

$queue = 'worker_queue';

$json = json_encode([
    'name' => 'timestamp::saver',
    'data' => [
        'timestamp' => time()
    ]
]);

foreach(range(0, 100) as $i) {
    if (!$r->lPush($queue, $json)) {
        echo 'can not push job to queue' . PHP_EOL;
    }

    echo "pushed a job" . PHP_EOL;

}