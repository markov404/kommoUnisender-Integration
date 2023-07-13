<?php

namespace Sdk;

use Interfaces\AmoApiClientFacade;
use Throwable;
use Sdk\AmoApiClient;

class AmoApiClientLazyAuth extends AmoApiClient implements AmoApiClientFacade
{
     /**
     * ApiService constructor.
     *
     * @param string $integrationId
     * @param string $integrationSecretKey
     * @param string $integrationRedirectUri
     */
    public function __construct(
        string $integrationId,
        string $integrationSecretKey,
        string $integrationRedirectUri
    ) {
        parent::__construct(
            $integrationId,
            $integrationSecretKey,
            $integrationRedirectUri
        );
    }
  /**
     * Получение токена досутпа для аккаунта.
     *
     * @param array $queryParams Входные GET параметры.
     * @param &$accessesContainer Container to put in accesses data.
     * @return string Имя авторизованного аккаунта.
     */
    public function auth(
        array $queryParams, 
        array &$accessesContainer = null): string
    {
  
        session_start();

        if (isset($queryParams['referer'])) {
            $this
                ->apiClient
                ->setAccountBaseDomain($queryParams['referer'])
                ->getOAuthClient()
                ->setBaseDomain($queryParams['referer']);
        }

        try {
            if (!isset($queryParams['code'])) {
                exit('No authorisation code has been send.');
            }
        } catch (Throwable $e) {
            die($e->getMessage());
        }

        try {
            $this->apiClient->getAccessToken();

            $accessToken = $this
                ->apiClient
                ->getOAuthClient()
                ->setBaseDomain($queryParams['referer'])
                ->getAccessTokenByCode($queryParams['code']);
            
            
            /**
             * Here we are checking if account_id is valid...
             * @var string $serviceID
             */
            // $serviceId = $_SESSION['service_id'];
            // error_log($serviceId);
            if (!$this->apiClient->isAccessTokenSet()) {
                $this->apiClient->setAccessToken($accessToken);
            }

            // $accountData = $this->apiClient->account()->getCurrent()->toArray();
            // $accountRealId = $accountData['id'];
            
            // if (intval($serviceId) !== intval($accountRealId)) {
            //     exit('Please send us real account id as query parameter');
            // }

            /** And saving accesses */
            if (!$accessToken->hasExpired()) {
                if (!is_null($accessesContainer)) {
                    /** Puting accesses data into &container */
                    $accessesContainer = array(
                        'base_domain' => $this->apiClient->getAccountBaseDomain(),
                        'access_token' => $accessToken->getToken(),
                        'refresh_token' => $accessToken->getRefreshToken(),
                        'expires' => $accessToken->getExpires(),
                    );
                }

                /** 
                 * Also saving data in tokens.json 
                 * for backward compatability purposes.
                 * 
                 * */
                $this->saveToken($_SESSION['service_id'], [
                    'base_domain' => $this->apiClient->getAccountBaseDomain(),
                    'access_token' => $accessToken->getToken(),
                    'refresh_token' => $accessToken->getRefreshToken(),
                    'expires' => $accessToken->getExpires(),
                ]);
            }
        } catch (Throwable $e) {
            die($e->getMessage());
        }

        session_abort();

        return $this
            ->apiClient
            ->getOAuthClient()
            ->getResourceOwner($accessToken)
            ->getName();
    }
}