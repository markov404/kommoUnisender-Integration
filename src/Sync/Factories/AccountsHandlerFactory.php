<?php

declare(strict_types=1);

namespace Sync\Factories;

use Abstractions\AbsCustomLogger;
use Abstractions\Types\SetOfModelManagers;
use Abstractions\AbsModelManager;

use Models\Accounts;
use Models\Integrations;

use Sync\Services\AccountsHandlerService;
use Sync\Handlers\AccountsHandler;
use Utils\Utils;

use Psr\Container\ContainerInterface;

/**
 * Class AccountsHandlerFactory
 *
 * @package Sync\Factories\AccountsHandlerFactory
 */
class AccountsHandlerFactory
{
    /**
     * Magick method __invoke
     * Returning new instance of AccountsHandler
     *
     * @param ContainerInterface $container
     * @return AccountsHandler
     */
    public function __invoke(ContainerInterface $container): AccountsHandler
    {
        Utils::loadEnvIfNotloadedAlready();
        $logsRootPath = $_ENV['LOGS_FOLDER_PATH'];
        $date = Utils::getCurrentDateForLogging();

        $service = new AccountsHandlerService();
        $logger = new AbsCustomLogger($service, $logsRootPath, $date);
        
        $modelManagers = new SetOfModelManagers();
        $modelManagers->addManagerToList('accounts', new AbsModelManager(Accounts::class));
        $modelManagers->addManagerToList('integrations', new AbsModelManager(Integrations::class));

        $service->setLogger($logger);
        $service->setModelManagersList($modelManagers);

        return new AccountsHandler($service);
    }
}
