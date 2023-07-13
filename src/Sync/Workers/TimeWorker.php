<?php

namespace Sync\Workers;

use Abstractions\BaseWorker;
use Pheanstalk\Pheanstalk;

/**
 * Class TimeWorker (for How-time command)
 * 
 * @package Sync\Workers\TimeWorker
 * @author mmarkov mmarkov@team.amocrm.com
 */
class TimeWorker extends BaseWorker
{
    /** @var Pheanstalk $connection */
    protected Pheanstalk $connection;

    /** @var string $queue = 'times' */
    protected string $queue = 'times';

    /**
     * Main functionality of processing any tasks
     * is should be implemented in this method.
     * 
     * @param string $data
     * @return void
     */
    public function process($data): void
    {
        // Just showing data...
        echo $data;
    }
}   