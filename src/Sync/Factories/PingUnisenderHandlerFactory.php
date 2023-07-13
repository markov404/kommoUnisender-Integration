<?php

declare(strict_types=1);

namespace Sync\Factories;

use Abstractions\AbsCustomLogger;
use Sync\Services\PingUnisenderService;
use Sync\Handlers\PingUnisenderHandler;
use Utils\Utils;

use Psr\Container\ContainerInterface;

/**
 * Class PingUnisenderHandlerFactory
 * Creating new instance of PingUnisenderHandler
 *
 * @package Sync\Factories\PingUnisenderHandlerFactory
 */
class PingUnisenderHandlerFactory
{
    /**
     * Magick method __invoke
     * Returning new instance of ContactsHandler
     *
     * @param ContainerInterface $container
     * @return PingUnisenderHandler
     */
    public function __invoke(ContainerInterface $container): PingUnisenderHandler
    {
        Utils::loadEnvIfNotloadedAlready();
        $logsRootPath = $_ENV['LOGS_FOLDER_PATH'];
        $date = Utils::getCurrentDateForLogging();

        $service = new PingUnisenderService();
        $logger = new AbsCustomLogger($service, $logsRootPath, $date);

        $service->setLogger($logger);

        return new PingUnisenderHandler($service);
    }
}
