<?php
namespace baohan\SwooleGearman\Queue;

use baohan\SwooleGearman\Router;
use Monolog\Logger;

class Worker
{
    /**
     * @var \GearmanWorker
     */
    private $r;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var string
     */
    private $name = "";

    /**
     * @var Logger
     */
    protected $log;

    /**
     * Worker constructor.
     * @param string $host
     * @param int $port
     * @param Logger $log
     */
    public function __construct($host = '127.0.0.1', $port = 6379, Logger $log)
    {
        $this->log = $log;
        $this->r = new \Redis();
        if(!$this->r->pconnect($host, $port)) {
            $this->log->crit("Can not connect redis server with {$host}:{$port}", [
                'host' => $host,
                'port' => $port
            ]);
        }
        $this->r->setOption(\Redis::OPT_READ_TIMEOUT, -1);
    }

    /**
     * Blocking for new task coming...
     *
     * @return void
     */
    public function listen()
    {
        while($val = $this->r->blPop($this->router->getListenQueueName(), 0)) {
            $payload = json_decode($val[1], true);
            if(!$payload) {
                $this->log->err('payload is empty or fails to parse from json...', $payload);
            } else {
                $this->log->debug("worker-".$this->name);
                $this->router->callback($payload);
            }
            $this->log->debug('done a job', $payload);
        }
        \swoole_process::wait(false);
    }

    /**
     * @param Router $router
     */
    public function addRouter(Router $router)
    {
        $this->router = $router;
    }

    public function setName($name)
    {
        $this->name = $name;
    }
}