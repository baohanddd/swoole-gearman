# swoole-gearman
A multi-processes worker framework based on Swoole and Gearman

Install
====



How
====

Quick start

```php

define('APP_PATH', realpath('.'));
define('DS', "::");

include APP_PATH . "/vendor/autoload.php";

$GLOBALS['cfg'] = include(APP_PATH . "/config/config.php");

try {
    $serv = new \baohan\SwooleGearman\Server();

    // custom handle $payload
    $serv->setDecode(function($payload) {
        return new Document($payload);
    });

    // custom callback event
    $serv->setEvtStart(function($serv) {
        echo "server start!" . PHP_EOL;
    });

    $serv->addCallback('user::created', function ($payload) {
        // doing something...
    });
    $serv->addCallback('user::updated', "\\App\\Job\\User::update");
    $serv->start();
} catch(\Exception $e) {
    echo "Caught Exception: " . $e->getMessage() . PHP_EOL;
}

```

Advanced initialize

```php



```

Configure
====

Event callbacks

