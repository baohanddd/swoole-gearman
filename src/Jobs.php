<?php
namespace baohan\SwooleGearman;

use baohan\SwooleGearman\Exception\ContextException;

class Jobs
{
    /**
     * @var array
     */
    protected $items = [];

    public function __construct()
    {
        $this->items = [];
    }

    /**
     * @param string $jobName
     * @param \Closure $closure
     */
    public function add(string $jobName, \Closure $closure)
    {
        $this->items[$jobName] = $closure;
    }

    /**
     * @param string $jobName
     * @return \Closure
     * @throws ContextException
     */
    public function getClosure(string $jobName): \Closure
    {
        if (!$this->has($jobName)) {
            throw new ContextException('Invalid registered job name', 422, [$jobName]);
        }
        return $this->items[$jobName];
    }

    /**
     * @param string $jobName
     * @return Job
     * @throws ContextException
     */
    public function getJob(string $jobName): Job
    {
        $closure = $this->getClosure($jobName);
        $job = $closure();
        if (!$job instanceof Job) {
            throw new ContextException('The job is not instance of Job', 423, [$jobName]);
        }
        return $job;
    }

    public function has(string $jobName): bool
    {
        return isset($this->items[$jobName]);
    }

    public function del(string $jobName): bool
    {
        if ($this->has($jobName)) {
            unset($this->items[$jobName]);
            return true;
        }
        return false;
    }
}