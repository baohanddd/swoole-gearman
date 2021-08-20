<?php
namespace baohan\SwooleGearman;

use baohan\SwooleGearman\Exception\ContextException;
use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Swoole\Server as SwooleServer;

class Server
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
    public $worker_num = 1;
    /**
     * @var int
     */
    public $task_worker_num = 4;
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
        $srv = new SwooleServer($this->host, $this->port);
        $srv->set([
            'worker_num' => $this->worker_num,
            'task_worker_num' => $this->task_worker_num,
            'task_ipc_mode' => 1,   // unix socket
            'task_max_request' => $this->task_max_request,
            'dispatch_mode' => 2,
        ]);
        $srv->on('Connect', function ($srv, $fd) {
            $this->log->debug("Client-{$fd}: Connect.");
        });
        $srv->on('receive', function ($srv, $fd, $reactor, $data) {
            $this->log->debug("[#".$srv->worker_id."]\tClient[$fd]: $data");
            $dstWorkerId = -1;
            $taskId = $srv->task($data, $dstWorkerId);
            $srv->send($fd, 'async task: '.$taskId."\n");
        });
        $srv->on('WorkerStart', function ($srv, $workerId) {
            global $argv;
            if ($workerId >= $srv->setting['worker_num']) {
                swoole_set_process_name("php {$argv[0]}: task_worker");
            } else {
                swoole_set_process_name("php {$argv[0]}: worker");
            }
            $this->log->debug("worker[#".$workerId."] started...");
        });
        $srv->on('Task', function ($srv, $taskId, $reactorId, $data) {
            $this->log->debug("worker[#{$srv->worker_id}] -> async task[id={$taskId}]...");
            try {
                $context = new Context($this->getPayload($data));
                $this->jobs->getJob($context->name)->execute($context->data, $srv->worker_id);
            } catch (\Throwable $e) {
                $this->log->error($e->getCode().' => '.$e->getMessage(), [$data]);
            }
            $srv->finish('ok');
        });
        $srv->on('Finish', function ($serv, $taskId, $data) {
            $this->log->debug("AsyncTask[{$taskId}] Finish: {$data}");
        });
        $srv->on('Close', function ($srv, $fd) {
            $this->log->debug("Client-{$fd}: Closed.");
        });
        $this->log->info('server starting...', [
            'host' => $this->host,
            'port' => $this->port,
            'worker_num' => $this->worker_num,
            'task_worker_num' => $this->task_worker_num,
            'task_max_request' => $this->task_max_request,
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
    
    /**
     * @param string $json
     * @return Collection
     * @throws ContextException
     */
    public function getPayload(string $json): Collection
    {
        $payload = json_decode($json, true);
        if (json_last_error()) {
            throw new ContextException(json_last_error_msg(), 420, [$json]);
        }
        return new Collection($payload);
    }
}