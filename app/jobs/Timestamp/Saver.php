<?php
namespace App\Job\Timestamp;

use baohan\SwooleGearman\Collection;
use baohan\SwooleGearman\Job;
use Monolog\Logger;

class Saver extends Job
{
    public function __construct(Logger $logger)
    {
        parent::__construct($logger);
    }

    public function execute(Collection $payload): bool
    {
//        var_dump($data);
    }
}