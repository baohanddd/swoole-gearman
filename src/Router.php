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
     * @return mixed|void
     */
    public function callback(array $context)
    {
        try {
            $class = $this->getJobClassName($context['name']);
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
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
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
     * @param $key
     * @return string
     */
    protected function getJobClassName($key) {
        $class = $this->prefix;
        $parts = explode('::', $key);
        foreach($parts as &$part) $part = $this->toCamelCase($part);
        $class .= implode("\\", $parts);
        return $class;
    }

    /**
     * @param string $name name with underscore
     * @return string
     */
    private function toCamelCase($name)
    {
        $cc = "";
        foreach(explode('_', $name) as $part) $cc .= ucwords($part);
        return $cc;
    }
}