<?php

namespace Sync\Services;

use Abstractions\Types\SetOfModelManagers;
use Interfaces\LoggerInterface;
use Managers\IntegrationsManager;
use Managers\AccountsManager;
use Utils\Utils;
use Abstractions\AbsService;
use Sdk\AmoApiClient;

use Throwable;

/**
 * Class ContactsService.
 * Extending AbsService.
 * Getting list of Kommo account contacts.
 *
 * @package Sync\Services\ContactService
 */
class ContactsService extends AbsService
{
    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param SetOfModelManagers $modelManagers
     */
    public function __construct(
        LoggerInterface $logger = null,
        SetOfModelManagers $modelManagers = null)
    {
        parent::__construct($logger, $modelManagers);
    }

    /**
     *
     * Дергать его отсюда.
     *
     * @param array $dataList
     * @return void
     */
    public function execute(array $dataList = array()): void
    {
        parent::execute($dataList);

        if (!$this->validateRequestParams($dataList, ['id'])) {
            $this->makeResponseObject([null], 400);
            return;
        }

        /** @var IntegrationsManager $integrationsManager */
        $integrationsManager = $this->setOfModelManagers->getManager('integrations');
        $integration = $integrationsManager->getIntegrationDataAsArray();

        if (is_null($integration)) {
            $this->makeResponseObject([
                'info' => 'There is not integration is setted up yet!'
            ], 500);
            return;
        }

        $apiClient = new AmoApiClient(
            $integration['id'],
            $integration['secret_key'],
            $integration['redirect_url']
        );

        $accountId = $dataList['id'];

        /** @var AccountsManager $accountsManager */
        $accountsManager = $this->setOfModelManagers->getManager('accounts');
        $accesses = $accountsManager->getAccountAccessesWithWhere('accounts_kommo_id = ' . $accountId);
        if (Utils::isCustomException($accesses) or is_null($accesses)) {
            $this->makeResponseObject([
                'info' => 'You have no acceses or authorised account...'
            ], 401);
            return;
        }

        $apiClient->setUpMandatoryCredentialsManually(
            $accesses['accesses_base_domain'],
            Utils::deCompressString($accesses['accesses_token']),
            Utils::deCompressString($accesses['accesses_refresh_token']),
            intval($accesses['accesses_expires']),
        );

        try {
            $contactsList = $apiClient->getContacts();
            $result = $contactsList;
            $code = 200;
        } catch(Throwable $e) {
            $this->writeErrorLogLine($e->getMessage());         
            $result = array(null);
            $code = 500;
        } finally {
            $this->makeResponseObject($result, $code);
        }

        return;
    }
}
