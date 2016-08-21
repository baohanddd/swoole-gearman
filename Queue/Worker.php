<?php
namespace baohan\SwooleGearman\Queue;


class Worker
{
    /**
     * @var \GearmanWorker
     */
    private $w;

    /**
     * custom unserialize method
     *
     * @var Callable
     */
    private $decode;

    public function __construct($host = '127.0.0.1', $port = 4730)
    {
        $this->w = new \GearmanWorker();
        $this->w->addServer($host, $port);

        $this->decode = function($payload) {
            return $payload;
        };
    }

    /**
     * Blocking for new task coming...
     *
     * @return void
     */
    public function listen()
    {
        while($this->w->work())
        {
            if ($this->w->returnCode() != GEARMAN_SUCCESS)
            {
                echo "return_code: " . $this->w->returnCode() . "\n";
                break;
            }
            \swoole_process::wait(false);
        }
    }

    /**
     * @param $key
     * @param $callback
     * @return string
     */
    public function addCallback($key, $callback) {
        $this->w->addCallback($key, function(\GearmanJob $context) use ($callback) {
            return call_user_func($callback, $this->decode($context->workload()));
        });
    }

    /**
     * @return Worker
     */
    public function getGearmanWorker()
    {
        return $this->w;
    }

    /**
     * @param Callable $decode
     */
    public function setDecode($decode)
    {
        $this->decode = $decode;
    }
}