<?php
namespace baohan\SwooleGearman;

use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Swoole\Process;

class Gearman
{
    /**
     * @var string
     */
    public $host = '127.0.0.1';
    /**
     * @var int
     */
    public $port = 9500;
    /**
     * @var int
     */
    public $worker_num = 10;
    /**
     * @var int
     * @deprecated
     */
    public $task_max_request = 500;
    
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
                $gmworker = new \GearmanWorker();
                $gmworker->addServer($this->host, $this->port);
                foreach ($this->jobs->getItems() as $key => $handle) {
                    $gmworker->addFunction($key, function ($job) use ($i, $log, $jobs) {
                        try {
                            $json = $job->workload();
                            $context = new Context($json);
                            return $jobs->getJob($context->name)->execute($context->data, $i);
                        } catch (\Throwable $e) {
                            $log->error($e->getCode() . ' => ' . $e->getMessage(), [$job->workload()]);
                            return false;
                        }
                    });
                }
                while ($gmworker->work()) {
                    if ($gmworker->returnCode() != GEARMAN_SUCCESS) {
                        $log->error("return_code: " . $gmworker->returnCode());
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