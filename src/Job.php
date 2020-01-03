<?php
namespace baohan\SwooleGearman;

use Monolog\Logger;

abstract class Job
{
    /**
     * @var Logger
     */
    protected $log;

    public function __construct(Logger $logger)
    {
        $this->log = $logger;
    }

    /**
     * @param Collection $payload
     * @return bool
     */
    abstract public function execute(Collection $payload): bool;
}