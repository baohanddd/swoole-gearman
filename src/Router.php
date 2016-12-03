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
        try {
            $class = $this->getJobClassName($context->functionName());
            $job = new $class;
            $decode = $this->decode;
            $job->{$this->executor}($decode($context->workload()));
            return 0;
        } catch (\Exception $e) {
            echo "Caught Exception: " . $e->getMessage() . PHP_EOL;
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