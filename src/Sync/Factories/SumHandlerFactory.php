<?php

declare(strict_types=1);

namespace Sync\Factories;

use Utils\Utils;

use Sync\Services\SumService;
use Sync\Handlers\SumHandler;
use Abstractions\AbsCustomLogger;

use Psr\Container\ContainerInterface;

/**
 * Class SumHandlerFactory.
 * Класс с фабричным методом для сборки SumHandler.
 *
 * @package Sync\Factories\SumHandlerFactory
 * @author mmarkov mmarkov@team.amocrm.com
 */
class SumHandlerFactory
{
    /**
     * Magick method for creating new instance of SumHandler
     *
     * @param ContainerInterface $container
     * @return SumHandler
     */
    public function __invoke(ContainerInterface $container): SumHandler
    {
        Utils::loadEnvIfNotloadedAlready();
        $logsRootPath = $_ENV['LOGS_FOLDER_PATH'];
        $date = Utils::getCurrentDateForLogging();

        $service = new SumService();
        $logger = new AbsCustomLogger($service, $logsRootPath, $date);

        $service->setLogger($logger);

        return new SumHandler($service);
    }
}
