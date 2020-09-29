<?php

use baohan\SwooleGearman\Exception\ContextException;
use baohan\SwooleGearman\Queue\RedisQueue;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use baohan\SwooleGearman\Queue\Worker;

class WorkerTest extends TestCase
{
    public function testConstruct()
    {
        $queue = $this->createMock(RedisQueue::class);
        $log = new Logger('worker');
        $worker = new Worker($queue, $log);
        $this->assertInstanceOf(Worker::class, $worker);
        return $worker;
    }

    /**
     * @depends testConstruct
     * @param Worker $worker
     */
    public function testGetPayload(Worker $worker)
    {
        try {
            $arr = ['a' => 1, 'b' => 1];
            $json = json_encode($arr);
            $worker->getPayload($json);
        } catch (Throwable $e) {
            $this->assertEquals($e->getMessage(), 'can not unserialize payload');
        }
    }
}