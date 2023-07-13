<?php

declare(strict_types=1);

namespace Sync\Factories;

use Config\ValidationTraitsConfig;
use Abstractions\AbsCustomLogger;
use Abstractions\Types\SetOfModelManagers;
use Abstractions\AbsModelManager;

use Models\Contacts;
use Models\Emails;
use Models\Accounts;

use Managers\AccountsManager;

use Sync\Services\SendContactsFromKommoService;
use Sync\Handlers\SendContactsFromKommoHandler;
use Utils\Utils;

use Psr\Container\ContainerInterface;

/**
 * Class SendContactsFromKommoHandlerFactory
 * Creating new instance of SendContactsFromKommoHandler
 *
 * @package Sync\Factories\SendContactsFromKommoHandlerFactory
 */
class SendContactsFromKommoHandlerFactory
{
    /**
     * Magick method __invoke
     * Returning new instance of ContactsHandler
     *
     * @param ContainerInterface $container
     * @return SendContactsFromKommoHandler
     */
    public function __invoke(ContainerInterface $container): SendContactsFromKommoHandler
    {
        Utils::loadEnvIfNotloadedAlready();
        $logsRootPath = $_ENV['LOGS_FOLDER_PATH'];
        $date = Utils::getCurrentDateForLogging();


        $service = new SendContactsFromKommoService();
        $logger = new AbsCustomLogger($service, $logsRootPath, $date);

        $modelManagers = new SetOfModelManagers();
        $modelManagers->addManagerToList('contacts', new AbsModelManager(Contacts::class));
        $modelManagers->addManagerToList('accounts', new AccountsManager(Accounts::class));
        $modelManagers->addManagerToList('emails', new AbsModelManager(Emails::class));

        $service->setLogger($logger);
        $service->setModelManagersList($modelManagers);
        $service->setTraitsConfig(new ValidationTraitsConfig($container));

        return new SendContactsFromKommoHandler($service);
    }
}
