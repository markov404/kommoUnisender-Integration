<?php

declare(strict_types=1);

namespace Sync\Factories;

use Abstractions\AbsCustomLogger;
use Abstractions\AbsModelManager;
use Abstractions\Types\SetOfModelManagers;

use Models\Integrations;
use Sync\Services\CreateIntegrationService;
use Sync\Handlers\CreateIntegrationHandler;

use Utils\Utils;

use Psr\Container\ContainerInterface;

/**
 * Class CreateIntegrationHandlerFactory
 * Creating new instance of CreateIntegrationHandler
 *
 * @package Sync\Factories\CreateIntegrationHandlerFactory
 */
class CreateIntegrationHandlerFactory
{
    /**
     * Magick method __invoke
     * Returning new instance of ContactsHandler
     *
     * @param ContainerInterface $container
     * @return CreateIntegrationHandler
     */
    public function __invoke(ContainerInterface $container): CreateIntegrationHandler
    {
        Utils::loadEnvIfNotloadedAlready();
        $logsRootPath = $_ENV['LOGS_FOLDER_PATH'];
        $date = Utils::getCurrentDateForLogging();

        $service = new CreateIntegrationService();
        $logger = new AbsCustomLogger($service, $logsRootPath, $date);

        $modelManagers = new SetOfModelManagers();
        $modelManagers->addManagerToList('integrations', new AbsModelManager(Integrations::class));

        $service->setLogger($logger);
        $service->setModelManagersList($modelManagers);

        return new CreateIntegrationHandler($service);
    }
}
