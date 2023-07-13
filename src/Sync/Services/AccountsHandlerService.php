<?php

namespace Sync\Services;

use Utils\Utils;

use Interfaces\LoggerInterface;
use Interfaces\ModelManagerInterface;
use Abstractions\AbsService;
use Abstractions\Types\SetOfModelManagers;


/**
 * Class AccountsHandlerService.
 * Extending AbsService.
 * Getting list of Kommo accounts registered in Sync application.
 *
 * @package Sync\Services\AccountsHandlerService
 */
class AccountsHandlerService extends AbsService
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

        /** Validating request parameters */
        if (!$this->validateRequestParams($dataList, ['integration_id'])) {
            $this->makeResponseObject([
                'info' => 'You should pass %integration_id% as' . 
                ' mandatory query parameter for this endpoint'
            ], 400);
            return;
        };

        /** @var ModelManagerInterface $integrationsManager */
        $integrationsManager = $this->setOfModelManagers->getManager('integrations');
        $response = $integrationsManager->entityWithId($dataList['integration_id']);

        if (Utils::isCustomException($response)) {
            $this->writeErrorLogLine($response->asPhpJson() . ' line60');
            $this->makeResponseObject([
                'info' => 'Something went wrong while checking an integration data.'
            ], 500);
            return;
        } else if ($response->count() != 1) {
            $this->makeResponseObject([
                'info' => 'There is no integration with this id.'
            ], 400);
            return;
        }

        /** @var ModelManagerInterface $accountsManager */
        $accountsManager = $this->setOfModelManagers->getManager('accounts');
        $response = $accountsManager->getAll();

        if (Utils::isCustomException($response)) {
            $this->writeErrorLogLine($response->asPhpJson() . ' line 76');
            $this->makeResponseObject([
                'info' => 'Something went wrong while getting all accounts list.'
            ], 500);
            return;
        }
        $accounts = $response;

        $accountsWithAccesses = array();
        foreach ($accounts as $account) {
            if (!is_null($account->access())) {
                array_push($accountsWithAccesses, $account['accounts_kommo_id']);
            }
        }

        $this->makeResponseObject([
            "accounts" => [
                "all" => $accounts->toArray(),
                "with_accesses" => $accountsWithAccesses
            ]
        ], 200);
        return;
    }
}
