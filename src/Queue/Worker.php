<?php
namespace baohan\SwooleGearman\Queue;

use baohan\SwooleGearman\Collection;
use baohan\SwooleGearman\Context;
use baohan\SwooleGearman\Exception\ContextException;
use baohan\SwooleGearman\Router;
use Monolog\Logger;
use Redis;

class Worker
{
    /**
     * @var Redis
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
     * @throws ContextException
     */
    public function __construct(string $host = '127.0.0.1', int $port = 6379, Logger $log)
    {
        $this->log = $log;
        $this->r = new Redis();
        if(!$this->r->pconnect($host, $port)) {
            throw new ContextException('Can not connect redis server', 510, [
                'host' => $host,
                'port' => $port
            ]);
        }
        $this->r->setOption(Redis::OPT_READ_TIMEOUT, -1);
    }

    /**
     * Blocking for new task coming...
     *
     * @return void
     * @throws ContextException
     */
    public function listen()
    {
        while($val = $this->r->blPop($this->router->getListenQueueName(), 0)) {
            try {
                $json = $val[1];
                $this->log->debug('raw serialize', $val);
                $payload = $this->getPayload($json);
                $this->log->debug('raw payload', $payload->all());
                $this->setExtra($payload->all());
                $context = new Context($payload);
                $this->log->debug("Running worker", [$this->name]);
                $this->router->callback($context);
            } catch (\Throwable $e) {
                $this->log->err($e->getMessage(), [$e->getCode() => $json]);
            }
            \swoole_process::wait(false);
        }
    }

    protected function setExtra(array $extra)
    {
        $this->log->pushProcessor(function ($record) use ($extra) {
            $record['extra'] = $extra;
            return $record;
        });
    }

    /**
     * @param string $json
     * @return Collection
     * @throws ContextException
     */
    protected function getPayload(string $json): Collection
    {
        $payload = json_decode(unserialize($json), true);
        if (json_last_error()) {
            throw new ContextException(json_last_error_msg(), 420, [$json]);
        }
        return new Collection($payload);
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