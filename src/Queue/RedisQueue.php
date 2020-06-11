<?php
namespace baohan\SwooleGearman\Queue;


class RedisQueue implements \baohan\SwooleGearman\Queue
{
    /**
     * @var \Redis
     */
    protected $r;

    public function __construct(\Redis $redis)
    {
        $redis->setOption(\Redis::OPT_READ_TIMEOUT, -1);
        $this->r = $redis;
    }

    /**
     * @inheritDoc
     */
    public function block(string $queueName)
    {
        $timeout = 0;
        return $this->r->blPop($queueName, $timeout);
    }
}