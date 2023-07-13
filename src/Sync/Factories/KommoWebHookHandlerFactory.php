<?php

declare(strict_types=1);

namespace Sync\Factories;

use Abstractions\AbsCustomLogger;
use Sync\Services\KommoWebHookService;
use Sync\Handlers\KommoWebHookHandler;
use Utils\Utils;

use Psr\Container\ContainerInterface;

/**
 * Class KommoWebHookHandlerFactory
 * Creating new instance of KommoWebHookHandler
 *
 * @package Sync\Factories\KommoWebHookHandlerFactory
 */
class KommoWebHookHandlerFactory
{
    /**
     * Magick method __invoke
     * Returning new instance of KommoWebHookHandler
     *
     * @param ContainerInterface $container
     * @return KommoWebHookHandler
     */
    public function __invoke(ContainerInterface $container): KommoWebHookHandler
    {
        Utils::loadEnvIfNotloadedAlready();
        $logsRootPath = $_ENV['LOGS_FOLDER_PATH'];
        $date = Utils::getCurrentDateForLogging();

        $service = new KommoWebHookService();
        $logger = new AbsCustomLogger($service, $logsRootPath, $date);

        $service->setLogger($logger);

        return new KommoWebHookHandler($service);
    }
}
