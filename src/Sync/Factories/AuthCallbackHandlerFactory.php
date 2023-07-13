<?php

declare(strict_types=1);

namespace Sync\Factories;

use Models\Integrations;
use Models\Accesses;
use Models\Accounts;

use Abstractions\Types\SetOfModelManagers;
use Abstractions\AbsModelManager;
use Abstractions\AbsCustomLogger;

use Sync\Services\AuthCallbackService;
use Sync\Handlers\AuthCallbackHandler;
use Utils\Utils;

use Psr\Container\ContainerInterface;

/**
 * Class AuthCallbackHandlerFactory
 *
 * @package Sync\Factories\AuthCallbackHandlerFactory
 */
class AuthCallbackHandlerFactory
{
    /**
     * Magick method __invoke
     * Returning new instance of AuthCallbackHandler
     *
     * @param ContainerInterface $container
     * @return AuthCallbackHandler
     */
    public function __invoke(ContainerInterface $container): AuthCallbackHandler
    {
        Utils::loadEnvIfNotloadedAlready();
        $logsRootPath = $_ENV['LOGS_FOLDER_PATH'];
        $date = Utils::getCurrentDateForLogging();

        $service = new AuthCallbackService();
        $logger = new AbsCustomLogger($service, $logsRootPath, $date);

        /** Initialising list of model managers and filling it. */
        $modelManagers = new SetOfModelManagers();
        $modelManagers->addManagerToList('integrations', new AbsModelManager(Integrations::class));
        $modelManagers->addManagerToList('accesses', new AbsModelManager(Accesses::class));
        $modelManagers->addManagerToList('accounts', new AbsModelManager(Accounts::class));

        $service->setLogger($logger);
        $service->setModelManagersList($modelManagers);

        return new AuthCallbackHandler($service);
    }
}
