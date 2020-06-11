<?php
namespace baohan\SwooleGearman;

/**
 * Interface Queue
 * @package baohan\SwooleGearman
 */
interface Queue
{
    /**
     * blocking to wait and receive new payload
     * @param string $queueName
     * @return mixed
     */
    public function block(string $queueName);
}