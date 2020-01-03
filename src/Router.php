<?php
namespace baohan\SwooleGearman;

use baohan\SwooleGearman\Exception\ContextException;

class Router
{
    /**
     * @var string
     */
    protected $queueName = "";

    /**
     * @var Jobs
     */
    public $jobs;

    public function __construct()
    {
        $this->jobs = new Jobs();
    }

    /**
     * @param Context $context
     * @throws ContextException
     */
    public function callback(Context $context): void
    {
        $this->jobs->getJob($context->name)->execute($context->data);
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
     * @param string $jobName
     * @param \Closure $closure
     */
    public function addCallback(string $jobName, \Closure $closure)
    {
        $this->jobs->add($jobName, $closure);
    }
}