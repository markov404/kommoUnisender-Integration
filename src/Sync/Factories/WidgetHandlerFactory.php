<?php

declare(strict_types=1);

namespace Sync\Factories;

use Models\Integrations;
use Models\Accesses;
use Models\Accounts;

use Abstractions\Types\SetOfModelManagers;
use Abstractions\AbsModelManager;
use Abstractions\AbsCustomLogger;

use Sync\Services\WidgetService;
use Sync\Handlers\WidgetHandler;
use Utils\Utils;

use Psr\Container\ContainerInterface;

/**
 * Class AuthCallbackHandlerFactory
 *
 * @package Sync\Factories\AuthCallbackHandlerFactory
 */
class WidgetHandlerFactory
{
    /**
     * Magick method __invoke
     * Returning new instance of AuthCallbackHandler
     *
     * @param ContainerInterface $container
     * @return WidgetHandler
     */
    public function __invoke(ContainerInterface $container): WidgetHandler
    {
        Utils::loadEnvIfNotloadedAlready();
        $logsRootPath = $_ENV['LOGS_FOLDER_PATH'];
        $date = Utils::getCurrentDateForLogging();

        $service = new WidgetService();
        $logger = new AbsCustomLogger($service, $logsRootPath, $date);

        /** Initialising list of model managers and filling it. */
        $modelManagers = new SetOfModelManagers();
        // $modelManagers->addManagerToList('integrations', new AbsModelManager(Integrations::class));
        // $modelManagers->addManagerToList('accesses', new AbsModelManager(Accesses::class));
        $modelManagers->addManagerToList('accounts', new AbsModelManager(Accounts::class));

        $service->setLogger($logger);
        $service->setModelManagersList($modelManagers);

        return new WidgetHandler($service);
    }
}
