<?php

namespace Sync\Factories\CommandFactories;

use Utils\Utils;
use Config\BeanstalkConfig;
use Sync\Commands\UpdateAccesses;
use Sync\Workers\AccessesWorker;

include './src/Sync/Commands/UpdateAccessesCommand.php';

use Psr\Container\ContainerInterface;

class UpdateAccessesCommandFactory
{
    public function __invoke(ContainerInterface $container): UpdateAccesses
    {
        Utils::bootEloquent();
        $command = new UpdateAccesses(
            new AccessesWorker(new BeanstalkConfig($container))
        );

        return $command;
    }
}