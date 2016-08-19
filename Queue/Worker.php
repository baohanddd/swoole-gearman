<?php
namespace baohan\SwooleGearman\Queue;


class Worker
{
    /**
     * @var \GearmanWorker
     */
    private $w;

    public function __construct($cfg)
    {
        $this->w = new \GearmanWorker();
        $this->w->addServer($cfg['host'], $cfg['port']);
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
     * @param $callable
     */
    public function addCallback($key, $callable)
    {
        $this->w->addFunction($key, $callable);
    }
}