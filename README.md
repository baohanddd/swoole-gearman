Swoole-gearman
====

A multi-processes worker framework based on Swoole.

Install
====

```
$ composer require baohan/swoole-gearman
```

But you should build a infrastructure with specified version of `swoole`, `gearman` and `php` first.

Since you could try docker image too.

```
> docker pull baohanddd/swoole-gearman:1.6.1

> docker run --rm --name=worker -v=$(pwd):/data -w=/data -p=9500:9500 baohanddd/swoole-gearman:1.6.1 php server.php
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

Now we try post job into Gearman Job server.

To do that, just need replace \baohan\SwooleGearman\Server to \baohan\SwooleGearman\Gearman.
please take a look, there isn't need `task_worker_num` option yet, `host` and `port` are same with gearman-job-server.

```php
use baohan\SwooleGearman\Collection;
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
```
On client side, we post job to gearman server instead of swoole server by gearman client.

```php
<?php
$gmc = new GearmanClient();
$gmc->addServer('gearman', '4730');
$data = [
    'name' => 'timestamp::print',
    'data' => [
        'message' => ''
    ]
];
for ($i = 0; $i < 1024; $i++) {
    $data['data']['message'] = $i + 1;
    $gmc->doBackground('timestamp::print', json_encode($data));
    if ($gmc->returnCode() != GEARMAN_SUCCESS) {
        echo "bad return code".PHP_EOL;
        exit;
    }
}
```
In this mode, The huge advantage is never lose jobs even worker server is crashed, all jobs are stored in Gearman.
On the other side, all clients only need to know gearman server address and port, we can deploy more than one 
instances of swoole server on difference machines more easily.

That's all.

