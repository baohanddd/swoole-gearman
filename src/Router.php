<?php
namespace baohan\SwooleGearman;

use Monolog\Logger;

class Router
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $executor;

    /**
     * custom unserialize method
     *
     * @var Callable
     */
    protected $decode;

    /**
     * @var Logger
     */
    protected $log;

    /**
     * @var string
     */
    protected $queueName = "";

    /**
     * @var array
     */
    protected $jobs = [];

    public function __construct(Logger $log)
    {
        $this->log = $log;
        $this->decode = function($payload) {
            return $payload;
        };
    }

    /**
     * @param Callable $decode
     */
    public function setDecode($decode)
    {
        $this->decode = $decode;
    }

    /**
     * @param array $context
     * @return int
     */
    public function callback(array $context)
    {
        try {
            if (!$this->validate($context)) {
                $this->log->err('Invalid context', $context);
                return 0;
            }
            if (!isset($this->jobs[$context['name']])) {
                $this->log->err('Invalid registered job name', $context);
                return 0;
            }
            $class = $this->jobs[$context['name']];
            $job = new $class;
            $decode = $this->decode;
            $job->{$this->executor}($decode($context['data']));
            return 0;
        } catch (\Exception $e) {
            $this->log->err($e->getMessage(), [$e->getTraceAsString()]);
            return $e->getCode();
        }
    }

    /**
     * @param array $context
     * @return bool
     */
    public function validate(array $context)
    {
        if (!isset($context['name']))     return false;
        if (!isset($context['data']))     return false;
        if (!is_array($context['data']))  return false;
        if (!is_string($context['name'])) return false;

        return true;
    }

    /**
     * @param string $name
     */
    public function setExecutor($name)
    {
        $this->executor = $name;
    }

    /**
     * @param string $name
     */
    public function setListenQueueName($name)
    {
        $this->queueName = $name;
    }

    /**
     * @return string
     */
    public function getListenQueueName()
    {
        return $this->queueName;
    }

    /**
     * @param $jobName
     * @param $className
     */
    public function addCallback($jobName, $className)
    {
        $this->jobs[$jobName] = $className;
    }
}