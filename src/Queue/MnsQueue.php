<?php
namespace baohan\SwooleGearman\Queue;

use AliyunMNS\Queue;

class MnsQueue implements \baohan\SwooleGearman\Queue
{
    /**
     * @var Queue
     */
    protected $r;

    /**
     * MnsQueue constructor.
     * @param Queue $queue
     */
    public function __construct(Queue $queue)
    {
        $this->r = $queue;
    }

    /**
     * @inheritDoc
     */
    public function block(string $queueName)
    {
        $waitSeconds = 30;
        return $this->r->receiveMessage($waitSeconds);
    }
}