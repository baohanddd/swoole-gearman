Swoole-gearman
====

A multi-processes worker framework based on Swoole.

Install
====

Install `swoole` first.

```
$ composer require baohan/swoole-gearman
```

How
====

### Quick start

It's a simple server, we run the server and start some workers are waiting for next job.

```php
use baohan\SwooleGearman\Collection;
use baohan\SwooleGearman\Server;
use Monolog\Logger;

require('vendor/autoload.php');

try {
    $s = new Server(Logger::INFO);
    $s->worker_num = 1;
    $s->task_worker_num = 19;
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
```
The server will be started
```
[2021-08-20 05:52:59] swoole-gearman.INFO: server starting... {"host":"127.0.0.1","port":9500,"worker_num":1,"task_worker_num":19,"task_max_request":500} []
```

Let's write a simple client:
```php
use Swoole\Client;

$client = new Client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 9500, 0.5))
{
    echo "connect failed. Error: {$client->errCode}\n";
}
$data = [
    'name' => 'timestamp::print',
    'data' => [
        'message' => ''
    ]
];
for ($i = 0; $i < 1024; $i++) {
    $data['data']['message'] = 'query on '.$i;
    $client->send(json_encode($data));
    echo $client->recv();
}
$client->close();
```
The results
```
......
worker[11] => query on 1019
worker[15] => query on 1023
worker[14] => query on 1022
worker[9] => query on 1017
worker[5] => query on 1013
worker[8] => query on 1016
worker[4] => query on 1012
worker[2] => query on 1010
worker[13] => query on 1021
worker[12] => query on 1020
worker[7] => query on 1015
......
```
That's all.

