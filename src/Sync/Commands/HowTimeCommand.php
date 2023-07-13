<?php

namespace Sync\Commands;

use Pheanstalk\Pheanstalk;
use Sync\Workers\TimeWorker;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

include './src/Sync/Workers/TimeWorker.php';

class HowTime extends Command
{

    /** $var string $currDate */
    private string $currDate;

    /** @var TimeWorker $workerForThisCommand */
    private TimeWorker $worker;

    /**
     * Constructor
     * 
     * Param current date
     * @param string $date
     */
    public function __construct(string $date, TimeWorker $worker)
    {
        parent::__construct();
        $this->currDate = $date;

        $this->worker = $worker;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->createTask('How-time: ' . $this->currDate);
        return 0;
    }

    public function createTask(string $data) 
    {
        Pheanstalk::create('localhost', 11300)->useTube('times')->put(json_encode($data));
        $this->worker->execute();
    }
}