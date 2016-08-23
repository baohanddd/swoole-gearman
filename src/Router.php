<?php
namespace baohan\SwooleGearman;

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

    public function __construct()
    {
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
     * @param \GearmanJob $context
     * @return mixed|void
     */
    public function callback(\GearmanJob $context)
    {
        $class = $this->getJobClassName($context->functionName());
        $job = new $class;
        $job->{$this->executor}($this->decode($context->workload()));
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
     * @param $key
     * @return string
     */
    protected function getJobClassName($key) {
        $class = $this->prefix;
        $parts = explode('::', $key);
        foreach($parts as &$part) $part = ucwords($part);
        $class .= implode("\\", $parts);
        return $class;
    }
}