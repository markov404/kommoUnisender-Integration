<?php

namespace Abstractions;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pheanstalk\Contract\PheanstalkInterface;
use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;
use Config\BeanstalkConfig;
use Throwable;

/**
 * Abstract class for any worker instance.
 * 
 * @package Abstractions\BaseWorker
 * @author mmarkov mmarkov@team.amocrm.com
 */
abstract class BaseWorker extends Command
{
    /** @var Pheanstalk $connection */
    protected Pheanstalk $connection;

    /** @var string $queue */
    protected string $queue = 'default';

    /** @var string $host */
    public string $host;

    /** @var string $port */
    public string $port;

    /** @var string $timeout */
    public string $timeout;

    /**
     * Constructor BaseWorker
     * @param BeanstalkConfig $beanstalk
     */
    final public function __construct(BeanstalkConfig $beanstalk)
    {
        parent::__construct();

        $this->host = $beanstalk->host;
        $this->port = $beanstalk->port;
        $this->timeout = $beanstalk->timeout;

        $this->connection = $beanstalk->getConnection();
    }

    /** Call from the CLI */
    public function execute(InputInterface $input = null, OutputInterface $output = null)
    {
        while ($job = $this->connection
        ->watchOnly($this->queue)
        ->ignore(PheanstalkInterface::DEFAULT_TUBE)
        ->reserve()
        ) {
            try {
                $this->process(json_decode(
                    $job->getData(),
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                ));
            } catch (Throwable $exception) {
                $this->handleException($exception, $job);
            }

            $this->connection->delete($job);
        }
    }

    /**
     * Handling exceptions
     * @param Throwable $exception
     * @param Job $job
     * @return void
     */
    private function handleException(Throwable $exception, Job $job): void
    {
        echo "Error Unhandled exception $exception" . PHP_EOL . $job->getData();
    }

    /** Proccessing tasks */
    abstract public function process($data);
}