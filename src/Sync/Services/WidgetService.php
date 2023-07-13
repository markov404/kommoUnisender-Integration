<?php

namespace Sync\Services;


use Abstractions\Types\SetOfModelManagers;
use Interfaces\LoggerInterface;
use Abstractions\AbsService;

use Laminas\Diactoros\Response\JsonResponse;


use Models\Integrations;
use Models\Accounts;

use Sdk\AmoApiClient;
use Unisender\ApiWrapper\UnisenderApi;

use Throwable;

/**
 * Class WidgetService.
 * Extending AbsService.
 * Getting list of Kommo account contacts.
 *
 * @package Sync\Services\WidgetService
 */
class WidgetService extends AbsService
{
    /**
     * Constructor
     *
     * @param LoggerInterface $logger
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

        if (!$this->validateRequestParams($dataList, ['unisender_key', 'account_id'])) {
            $this->makeResponseObject([null], 400);
            return;
        }
            
        $targetAccount = Accounts::whereAccountsKommoId(intval($dataList['account_id']))->first();
        error_log($targetAccount['accounts_kommo_id']);
        if (!isset($targetAccount)) {
            $this->makeResponseObject([
                'Widget service will not work if you are not authorised.'
            ], 400);
            return;
        }

        $targetAccount->unisender_key = $dataList['unisender_key'];
        $targetAccount->save();
        

        /** WEBHOOK SUBSCRIPTION */

        $integration = Integrations::all()->first();

        if (!isset($integration)) {
            $this->makeResponseObject([
                'Integration has not been set yet.'
            ], 400);
            return;
        }

        $apiClient = new AmoApiClient(
            $integration->integrations_id,
            $integration->integrations_secret_key,
            $integration->integrations_redirect_url
        );


        $webHookModel = (new \AmoCRM\Models\WebhookModel())
                        ->setSettings([
                            'add_contact',
                            'update_contact',
                            'delete_contact'
                        ])
                        ->setDestination('https://ff54-173-233-147-68.ngrok-free.app/webhook');
        
        $response = $apiClient->subscribeToWebHook($webHookModel);
        
        $this->laminasJsonResponse = new JsonResponse($response);
    }
}
