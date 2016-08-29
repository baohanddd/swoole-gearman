<?php
namespace baohan\SwooleGearman;

use baohan\SwooleGearman\Queue\Worker;

class Server
{
    /**
     * @var int
     */
    private $worker_num = 4;

    /**
     * @var int
     */
    private $reactor_num = 2;

    /**
     * @var string
     */
    private $swoole_host = '127.0.0.1';

    /**
     * @var int
     */
    private $swoole_port = 9500;

    /**
     * @var bool
     */
    private $daemonize = false;

    /**
     * @var Worker
     */
    private $w;

    /**
     * @var Callable
     */
    private $evt_start;

    /**
     * @var Callable
     */
    private $evt_connect;

    /**
     * @var Callable
     */
    private $evt_receive;

    /**
     * @var Callable
     */
    private $evt_close;

    /**
     * @var Callable
     */
    private $evt_shutdown;

    /**
     * @var Callable
     */
    private $evt_worker_start;

    public function __construct(Worker $worker)
    {
//        $this->w = new Worker(['host' => $this->gearman_host, 'port' => $this->gearman_port]);
        $this->w = $worker;
    }

    public function start()
    {
        $serv = new \swoole_server($this->swoole_host, $this->swoole_port);
        $serv->set(['worker_num' => $this->worker_num, 'reactor_num' => $this->reactor_num, 'daemonize' => $this->daemonize]);
        $serv->on('Start',       [$this, 'onStart']);
        $serv->on('Connect',     [$this, "onConnect"]);
        $serv->on('Receive',     [$this, "onReceive"]);
        $serv->on('Close',       [$this, "onClose"]);
        $serv->on('WorkerStart', [$this, "onWorkerStart"]);
        $serv->on('Shutdown',    [$this, "onShutdown"]);

        $GLOBALS['atomic'] = new \swoole_atomic(10);

        $serv->start();
    }

    public function onStart($serv) {
        if(is_callable($this->evt_start)) {
            call_user_func($this->evt_start, $serv);
        }
    }

    public function onConnect($serv, $fd) {
        if(is_callable($this->evt_connect)) {
            call_user_func($this->evt_connect, $serv, $fd);
        }
    }

    public function onReceive($serv, $fd, $from_id, $data) {
        if(is_callable($this->evt_receive)) {
            call_user_func($this->evt_receive, $serv, $fd, $from_id, $data);
        }
    }

    public function onClose($serv, $fd) {
        if(is_callable($this->evt_close)) {
            call_user_func($this->evt_close, $serv, $fd);
        }
    }

    public function onShutdown($serv)
    {
        if(is_callable($this->evt_shutdown)) {
            call_user_func($this->evt_shutdown, $serv);
        }
    }

    public function onWorkerStart($serv, $workerId) {
        if ($workerId >= $serv->setting['worker_num']) {
            return true;
        }
        $GLOBALS['worker_id'] = $workerId;
        echo "worker{$workerId} start" . PHP_EOL;

        if(is_callable($this->evt_worker_start)) {
            call_user_func($this->evt_worker_start, $serv, $workerId);
        }

        $this->w->listen();
    }

    /**
     * @return int
     */
    public function getWorkerNum()
    {
        return $this->worker_num;
    }

    /**
     * @param int $worker_num
     */
    public function setWorkerNum($worker_num)
    {
        $this->worker_num = (int) $worker_num;
    }

    /**
     * @return int
     */
    public function getReactorNum()
    {
        return $this->reactor_num;
    }

    /**
     * @param int $reactor_num
     */
    public function setReactorNum($reactor_num)
    {
        $this->reactor_num = (int) $reactor_num;
    }

    /**
     * @return string
     */
    public function getSwooleHost()
    {
        return $this->swoole_host;
    }

    /**
     * @param string $swoole_host
     */
    public function setSwooleHost($swoole_host)
    {
        $this->swoole_host = $swoole_host;
    }

    /**
     * @return int
     */
    public function getSwoolePort()
    {
        return $this->swoole_port;
    }

    /**
     * @param int $swoole_port
     */
    public function setSwoolePort($swoole_port)
    {
        $this->swoole_port = (int) $swoole_port;
    }

    /**
     * @return boolean
     */
    public function isDaemonize()
    {
        return $this->daemonize;
    }

    /**
     * @param boolean $daemonize
     */
    public function setDaemonize($daemonize)
    {
        $this->daemonize = (bool) $daemonize;
    }

    /**
     * @param Callable $evt_start
     */
    public function setEvtStart(callable $evt_start)
    {
        $this->evt_start = $evt_start;
    }

    /**
     * @param Callable $evt_connect
     */
    public function setEvtConnect(callable $evt_connect)
    {
        $this->evt_connect = $evt_connect;
    }

    /**
     * @param Callable $evt_receive
     */
    public function setEvtReceive(callable $evt_receive)
    {
        $this->evt_receive = $evt_receive;
    }

    /**
     * @param Callable $evt_close
     */
    public function setEvtClose(callable $evt_close)
    {
        $this->evt_close = $evt_close;
    }

    /**
     * @param Callable $evt_shutdown
     */
    public function setEvtShutdown(callable $evt_shutdown)
    {
        $this->evt_shutdown = $evt_shutdown;
    }

    /**
     * @param Callable $evt_worker_start
     */
    public function setEvtWorkerStart(callable $evt_worker_start)
    {
        $this->evt_worker_start = $evt_worker_start;
    }
}