<?php

namespace Sync\Workers;

use Abstractions\BaseWorker;
use Utils\Utils;
use Pheanstalk\Pheanstalk;
use Sdk\AmoApiClient;
use Models\Integrations;
use Models\Accounts;
use Models\Accesses;
use Managers\AccountsManager;
use Managers\IntegrationsManager;


/**
 * Class AccessesWorker (for UpdateAccessesCommand)
 * 
 * @package Sync\Workers\AccessesWorker
 * @author mmarkov mmarkov@team.amocrm.com
 */
class AccessesWorker extends BaseWorker
{
    /** @var Pheanstalk $connection */
    protected Pheanstalk $connection;

    /** @var string $queue */
    protected string $queue = 'accesses';

    /**
     * Main functionality of processing any tasks
     * is should be implemented in this method.
     * 
     * @param array $accountIdsList
     * @return void
     */
    public function process($accountIdsList): void
    {
        $integrationManager = new IntegrationsManager(Integrations::class);
        $integration = $integrationManager->getIntegrationDataAsArray();

        if (is_null($integration)) {
            echo 'There is no integration setted up yet!';
            return;
        }

        $accountsManager = new AccountsManager(Accounts::class);

        $amoApiClient = new AmoApiClient(
            $integration['id'],
            $integration['secret_key'],
            $integration['redirect_url']
        );

        foreach ($accountIdsList as $accountId) {
            $this->updateAccessesForAccount(
                $accountId, 
                $amoApiClient, 
                $accountsManager
            );
        }
    }

    /**
     * Updating accesses for specific account
     * 
     * @param int $accountKommoId
     * @return void
     */
    public function updateAccessesForAccount(
        int $accountKommoId,
        AmoApiClient $apiClient,
        AccountsManager $accountsManager): void
    {
        echo 'Updating token for ' . $accountKommoId . ' account...' . "\n";

        $accesses = $accountsManager->getAccountAccessesWithWhere('accounts_kommo_id = ' . $accountKommoId);
        if (Utils::isCustomException($accesses)) {
            echo 'Something wrong with getting accesses of account with id = ' . $accountKommoId;
        }
        if (is_null($accesses)) { return; }
        
        $newAccessToken = $apiClient->getNewTokenByRefreshToken(
            Utils::deCompressString($accesses['accesses_token']),
            Utils::deCompressString($accesses['accesses_refresh_token']),
            intval($accesses['accesses_expires']),
            $accesses['accesses_base_domain']
        );
        
        $accessToken = $newAccessToken->getToken();
        $refreshToken = $newAccessToken->getRefreshToken();
        $expires = $newAccessToken->getExpires();

        Accesses::where('accesses_fk_account_kommo_id', $accountKommoId)->update([
            'accesses_token' => Utils::compressString($accessToken),
            'accesses_refresh_token' => Utils::compressString($refreshToken),
            'accesses_expires' => intval($expires)
        ]);

        echo 'Token for ' . $accountKommoId . ' account updated.' . "\n";
    }
}   