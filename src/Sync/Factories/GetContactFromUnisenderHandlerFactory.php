<?php

declare(strict_types=1);

namespace Sync\Factories;

use Abstractions\AbsCustomLogger;
use Sync\Services\GetContactFromUnisenderService;
use Sync\Handlers\GetContactFromUnisenderHandler;

use Utils\Utils;

use Psr\Container\ContainerInterface;

/**
 * Class GetContactFromUnisenderHandlerFactory
 * Creating new instance of GetContactFromUnisenderHandler
 *
 * @package Sync\Factories\GetContactFromUnisenderHandlerFactory
 */
class GetContactFromUnisenderHandlerFactory
{
    /**
     * Magick method __invoke
     * Returning new instance of ContactsHandler
     *
     * @param ContainerInterface $container
     * @return GetContactFromUnisenderHandler
     */
    public function __invoke(ContainerInterface $container): GetContactFromUnisenderHandler
    {
        Utils::loadEnvIfNotloadedAlready();
        $logsRootPath = $_ENV['LOGS_FOLDER_PATH'];
        $date = Utils::getCurrentDateForLogging();

        $service = new GetContactFromUnisenderService();
        $logger = new AbsCustomLogger($service, $logsRootPath, $date);

        $service->setLogger($logger);

        return new GetContactFromUnisenderHandler($service);
    }
}
