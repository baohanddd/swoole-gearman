<?php
namespace baohan\SwooleGearman\Queue;


use baohan\SwooleGearman\Router;

class Worker
{
    /**
     * @var \GearmanWorker
     */
    private $w;

    /**
     * @var Router
     */
    private $router;

    public function __construct($host = '127.0.0.1', $port = 4730)
    {
        $this->w = new \GearmanWorker();
        $this->w->addServer($host, $port);
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
     * @param Router $router
     */
    public function addRouter(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param $key
     * @return string
     */
    public function addCallback($key) {
        $this->w->addFunction($key, function(\GearmanJob $context) {
            return $this->router->callback($context);
        });
    }

    /**
     * @return Worker
     */
    public function getGearmanWorker()
    {
        return $this->w;
    }
}