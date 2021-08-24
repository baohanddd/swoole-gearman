<?php
namespace baohan\SwooleGearman;

use baohan\SwooleGearman\Exception\ContextException;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Swoole\Server as SwooleServer;

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
        $srv = new SwooleServer('127.0.0.1', '9500');
        $srv->set([
            'worker_num' => $this->worker_num,
            'dispatch_mode' => 1,
        ]);
//        $srv->on('Connect', function ($srv, $fd) {
//            $this->log->debug("Client-{$fd}: Connect.");
//        });
        $srv->on('receive', function ($srv, $fd, $reactor, $data) {
            $this->log->debug("[#".$srv->worker_id."]\tClient[$fd]: $data");
//            $dstWorkerId = -1;
//            $taskId = $srv->task($data, $dstWorkerId);
//            $srv->send($fd, 'async task: '.$taskId."\n");
        });
        $srv->on('WorkerStart', function ($srv, $workerId) {
            global $argv;
            if ($workerId >= $srv->setting['worker_num']) {
                swoole_set_process_name("php {$argv[0]}: task_worker");
            } else {
                swoole_set_process_name("php {$argv[0]}: worker");
            }
            $this->log->info("worker[#".$workerId."] started...");
            $gmworker = new \GearmanWorker();
            $gmworker->addServer($this->host, $this->port);
            foreach ($this->jobs->getItems() as $key => $handle) {
                $gmworker->addFunction($key, function ($job) use ($srv) {
                    try {
                        $json = $job->workload();
                        $this->log->debug('new job is coming - '.$json);
                        $context = new Context($json);
                        return $this->jobs->getJob($context->name)->execute($context->data, $srv->worker_id);
                    } catch (\Throwable $e) {
                        $this->log->error($e->getCode().' => '.$e->getMessage(), [$job->workload()]);
                        return false;
                    }
                });
            }
            while ($gmworker->work()) {
                if ($gmworker->returnCode() != GEARMAN_SUCCESS) {
                    $this->log->error("return_code: ".$gmworker->returnCode());
//                    break;
                }
            }
        });
        $srv->on('Finish', function ($serv, $taskId, $data) {
            $this->log->debug("AsyncTask[{$taskId}] Finish: {$data}");
        });
//        $srv->on('Close', function ($srv, $fd) {
//            $this->log->debug("Client-{$fd}: Closed.");
//        });
        $this->log->info('server starting...', [
            'host' => $this->host,
            'port' => $this->port,
            'worker_num' => $this->worker_num,
        ]);
        $srv->start();
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