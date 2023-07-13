<?php

declare(strict_types=1);

namespace Sync\Factories;

use Abstractions\AbsCustomLogger;
use Abstractions\Types\SetOfModelManagers;
use Managers\AccountsManager;
use Managers\IntegrationsManager;
use Models\Integrations;
use Models\Accounts;
use Sync\Services\ContactsService;
use Sync\Handlers\ContactsHandler;
use Utils\Utils;

use Psr\Container\ContainerInterface;

/**
 * Class ContactsHandlerFactory
 * Creating new instance of ContactsHandler
 *
 * @package Sync\Factories\ContactsHandlerFactory
 */
class ContactsHandlerFactory
{
    /**
     * Magick method __invoke
     * Returning new instance of ContactsHandler
     *
     * @param ContainerInterface $container
     * @return ContactsHandler
     */
    public function __invoke(ContainerInterface $container): ContactsHandler
    {
        Utils::loadEnvIfNotloadedAlready();
        $logsRootPath = $_ENV['LOGS_FOLDER_PATH'];
        $date = Utils::getCurrentDateForLogging();

        $service = new ContactsService();
        $logger = new AbsCustomLogger($service, $logsRootPath, $date);

        $modelManagers = new SetOfModelManagers();
        $modelManagers->addManagerToList(
            'integrations', new IntegrationsManager(Integrations::class));
        $modelManagers->addManagerToList(
            'accounts', new AccountsManager(Accounts::class));

        $service->setLogger($logger);
        $service->setModelManagersList($modelManagers);

        return new ContactsHandler($service);
    }
}
