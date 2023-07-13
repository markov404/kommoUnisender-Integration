<?php

namespace Sync\Services;

use Interfaces\AmoApiClientFacade;
use Interfaces\LoggerInterface;
use Abstractions\Types\SetOfModelManagers;
use Abstractions\AbsService;

use Throwable;
use Sdk\AmoApiClientLazyAuth;
use Utils\Utils;

use Models\Integrations;
use Sdk\AmoApiClient;

/**
 * Class AuthCallbackService.
 *
 * Extending AbsService.
 * Managing callback from AmoCrmAPI
 *
 * @package Sync\Services\AuthCallbackService
 */
class AuthCallbackService extends AbsService
{
    /**
     *
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
     * @param array $dataList input data.
     * @return void
     */
    public function execute(array $dataList = array()): void
    {
        parent::execute($dataList);

        if (!$this->validateRequestParams(
            $dataList, ['code', 'state', 'referer', 'platform', 'client_id'])
            and !$this->validateRequestParams($dataList, [
                'code', 'referer', 'platform', 'client_id', 'from_widget'
            ])) {    
            $this->makeResponseObject([null], 400);
            return;
        }
        
        $integrationsManager = $this->setOfModelManagers->getManager('integrations');
        $integrationsData = $integrationsManager->getFirst();
        
        /** Checking db request on exception state */
        if (Utils::isCustomException($integrationsData)) {
            $this->writeErrorLogLine($integrationsData->asPhpJson() . ' line63.');
            $this->makeResponseObject([
                "info" => "Something went wrong while getting integration data."
            ], 500);
            return;
        }

        if (is_null($integrationsData)) {
            $this->makeResponseObject([
                'info' => 'The integrations is not setted up yet.'
            ], 400);
            return;
        }
        
        $integrationId = $integrationsData['integrations_id'];

        $apiClient = $this->fabricOfAMOApiClient($integrationsData, $dataList);
        $accessesContainer = array(); // It is being passed via LINK(&).
        $accountName = $apiClient->auth($dataList, $accessesContainer);
        

        /** All that below is saving all data */
        $accountId = null;
        try {
            /**
             * to @dvishnevski
             * 
             * how better in PHP style:
             * 
             * %$apiClient->getAccountInformation()['id']% OR
             * %$apiClient->getAccountInformation('id')% ??????
             */
            $accountId = $apiClient->getAccountInformation()['id'];
        } catch (Throwable $e) {
            $this->makeResponseObject([
                "Something went wrong with kommo API side"
            ], 500);
            return;
        }

         
        // TODO: create databaseRequests.php for covering multiple related
        // requests in transactions.

        /** Initialising account requests manager and writing a record */
        $accountsManager = $this->setOfModelManagers->getManager('accounts');
        $response = $accountsManager->create([
            "accounts_kommo_id" => intval($accountId),
            "accounts_fk_integrations_id" => $integrationId
        ]);

        if (Utils::isCustomException($response)) {
            $this->writeErrorLogLine($response->asPhpJson() . ' line 115.');
            $this->makeResponseObject([
                "info" => "Something went wrong while saving your account."
            ], 500);
            return;
        }

        /** Initialising accesses requests manager and writing a record */
        $accessesManager = $this->setOfModelManagers->getManager('accesses');
        $response = $accessesManager->create([
            'accesses_token' => Utils::compressString($accessesContainer['access_token']),
            'accesses_base_domain' => $accessesContainer['base_domain'],
            'accesses_refresh_token' => Utils::compressString($accessesContainer['refresh_token']),
            'accesses_expires' => $accessesContainer['expires'],
            'accesses_fk_account_kommo_id' => intval($accountId),
        ]);

        if (Utils::isCustomException($response)) {
            $this->writeErrorLogLine($response->asPhpJson() . ' line131.');
            $this->makeResponseObject([
                "info" => "Something went wrong while saving your accesses data."
            ], 500);
            return;
        }

        $this->makeResponseObject([
            'name' => $accountName,
            'info' => 'You are succesfully authorised, pass your account_id' .
                ' to every secured endpoint in order to be able access it.',
        ], 200);

        return;
    }

    /**
     * Fabric method, returning an implementation of 
     * AmoApiClientFacade interface.
     * 
     * @param Integrations $integration
     * @param array $queryDataList
     * 
     * @return AmoApiClientFacade
     */
    public function fabricOfAMOApiClient(
        Integrations $integration,
        array $queryDataList
    ): AmoApiClientFacade
    {
        $integrationId = $integration['integrations_id'];
        $integrationSecretKey = $integration['integrations_secret_key'];
        $integrationRedirectUrl = $integration['integrations_redirect_url'];

        $result = null;

        if (array_key_exists('from_widget', $queryDataList)) {
            error_log($integrationId);
            $result = new AmoApiClientLazyAuth(
                $integrationId,
                $integrationSecretKey,
                $integrationRedirectUrl
            );
        } else {
            $result = new AmoApiClient(
                $integrationId,
                $integrationSecretKey,
                $integrationRedirectUrl
            );
        }

        return $result;
    }
}
