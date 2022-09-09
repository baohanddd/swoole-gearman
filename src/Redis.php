<?php
namespace baohan\SwooleGearman;

use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Swoole\Process;

class Redis
{
    /**
     * @var string
     */
    public $host = 'redis';
    /**
     * @var int
     */
    public $port = 6379;
    /**
     * Redis AUTH string
     * @var string
     * @example username:password
     */
    public $auth = "";
    /**
     * List Key Name
     * @var string
     * @example jobs
     */
    public $key = "jobs";
    /**
     * @var int
     */
    public $worker_num = 2;
    /**
     * @var Logger
     */
    protected $log;
    /**
     * @var Jobs
     */
    public $jobs;
    /**
     * Created process
     * @var array
     */
    protected $processes = [];
    
    /**
     * @param int $logLevel
     * @throws Exception
     */
    public function __construct(int $logLevel = Logger::DEBUG)
    {
        $this->log = new Logger('swoole-gearman');
        $this->log->pushHandler(new StreamHandler('php://stdout', $logLevel));
        $this->jobs = new Jobs();
    }
    
    public function start()
    {
        $this->createProcesses($this->worker_num, $this->log, $this->jobs);
        for ($i = $this->worker_num; $i--;) {
            $status = Process::wait(true);
            $this->log->debug("Recycled #{$status['pid']}, code={$status['code']}, signal={$status['signal']}");
        }
        $this->log->debug('All workers are exit...');
    }

    /**
     * @param int $workerNumber
     * @param Logger $log
     * @param Jobs $jobs
     */
    protected function createProcesses(int $workerNumber, Logger $log, Jobs $jobs): void
    {
        for ($i = 0; $i < $workerNumber; $i++) {
            $process = new Process(function () use ($i, $log, $jobs) {
                $NO = $i;
                $redis = new \Redis();
                $redis->pconnect($this->host, $this->port);
                $redis->setOption(\Redis::OPT_READ_TIMEOUT, -1);
                if ($this->auth) {
                    if (!$redis->auth($this->auth)) {
                        $log->critical('Can not connect to redis server');
                        exit;
                    }
                }
                while (true) {
                    $data = $redis->brPop($this->key, 0);
                    try {
                        $context = new Context($data[1]);
                        $this->jobs->getJob($context->name)->execute($context->data, $NO);
                    } catch (\Throwable $e) {
                        $this->log->error($e->getCode().' => '.$e->getMessage(), [$data]);
                    }
                }
            });
            $process->start();
            $log->debug("worker[#{$process->pid}] started...#{$i}");
            $this->processes[] = $process;
        }
    }
    
    /**
     * @param string $jobName
     * @param \Closure $closure
     */
    public function addCallback(string $jobName, \Closure $closure)
    {
        $this->jobs->add($jobName, $closure);
    }
}