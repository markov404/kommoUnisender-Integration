<?php

namespace Sync\Factories\CommandFactories;

use Config\BeanstalkConfig;
use Sync\Workers\TimeWorker;
use Sync\Commands\HowTime;

include './src/Sync/Commands/HowTimeCommand.php';

use Psr\Container\ContainerInterface;

class HowTimeCommandFactory
{
    public function __invoke(ContainerInterface $container): HowTime
    {
        $command = new HowTime(
            strval(date("H:i (d.Y)")),
            new TimeWorker(new BeanstalkConfig($container))
        );

        return $command;
    }
}