<?php
namespace baohan\SwooleGearman;

abstract class Job
{
    /**
     * @param Collection $payload
     * @return bool
     */
    abstract public function execute(Collection $payload, int $workerId): bool;
}