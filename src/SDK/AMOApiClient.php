<?php

namespace Sdk;

use Interfaces\AmoApiClientFacade;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Filters\PagesFilter;
use AmoCRM\Collections\ContactsCollection;
use League\OAuth2\Client\Token\AccessToken;

use Exception;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Throwable;

/**
 * Class ApiClient.
 * Use it for accessing kommoAPI purposes.
 *
 * @package Sdk\AmoApiClient
 */
class AmoApiClient implements AmoApiClientFacade
{
    /** @var string Базовый домен авторизации. */
    protected const TARGET_DOMAIN = 'kommo.com';

    /** @var string Файл хранения токенов. */
    protected const TOKENS_FILE = './tokens.json';

    /** @var AmoCRMApiClient AmoCRM клиент. */
    protected AmoCRMApiClient $apiClient;

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
        $this->apiClient = new AmoCRMApiClient(
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

        /** Занесение системного идентификатора в сессию для реализации OAuth2.0. */
        if (!empty($queryParams['id'])) {
            $_SESSION['service_id'] = $queryParams['id'];
        }

        if (isset($queryParams['referer'])) {
            $this
                ->apiClient
                ->setAccountBaseDomain($queryParams['referer'])
                ->getOAuthClient()
                ->setBaseDomain($queryParams['referer']);
        }

        try {
            if (!isset($queryParams['code'])) {
                $state = bin2hex(random_bytes(16));
                $_SESSION['oauth2state'] = $state;
                if (isset($queryParams['button'])) {
                    echo $this
                        ->apiClient
                        ->getOAuthClient()
                        ->setBaseDomain(self::TARGET_DOMAIN)
                        ->getOAuthButton([
                            'title' => 'Установить интеграцию',
                            'compact' => true,
                            'class_name' => 'className',
                            'color' => 'default',
                            'error_callback' => 'handleOauthError',
                            'state' => $state,
                        ]);
                } else {
                    $authorizationUrl = $this
                        ->apiClient
                        ->getOAuthClient()
                        ->setBaseDomain(self::TARGET_DOMAIN)
                        ->getAuthorizeUrl([
                            'state' => $state,
                            'mode' => 'post_message',
                        ]);
                    header('Location: ' . $authorizationUrl);
                }
                die;
            } elseif (
                empty($queryParams['state']) ||
                empty($_SESSION['oauth2state']) ||
                ($queryParams['state'] !== $_SESSION['oauth2state'])
            ) {
                unset($_SESSION['oauth2state']);
                exit('Invalid state');
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
            $serviceId = $_SESSION['service_id'];
            if (!$this->apiClient->isAccessTokenSet()) {
                $this->apiClient->setAccessToken($accessToken);
            }

            $accountData = $this->apiClient->account()->getCurrent()->toArray();
            $accountRealId = $accountData['id'];
            
            if (intval($serviceId) !== intval($accountRealId)) {
                exit('Please send us real account id as query parameter');
            }

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

    /**
     * Сохранение токена авторизации.
     *
     * @param int $serviceId Системный идентификатор аккаунта.
     * @param array $token Токен доступа Api.
     * @return void
     */
    protected function saveToken(int $serviceId, array $token): void
    {
        $tokens = file_exists(self::TOKENS_FILE)
            ? json_decode(file_get_contents(self::TOKENS_FILE), true)
            : [];
        $tokens[$serviceId] = $token;
        file_put_contents(self::TOKENS_FILE, json_encode($tokens, JSON_PRETTY_PRINT));
    }

    /**
     * Получение токена из файла.
     *
     * @param int $serviceId Системный идентификатор аккаунта.
     * @return AccessToken
     */
    public function readToken(int $serviceId): AccessToken
    {
        try {
            if (!file_exists(self::TOKENS_FILE)) {
                throw new Exception('Tokens file not found.');
            }

            $accesses = json_decode(file_get_contents(self::TOKENS_FILE), true);
            if (empty($accesses[$serviceId])) {
                throw new Exception("Unknown account name \"$serviceId\".");
            }

            $accessToken = new AccessToken($accesses[$serviceId]);
            return $accessToken;
        } catch (Throwable $e) {
            exit($e->getMessage());
        }
    }

    /**
     * Get BaseDomain from file
     *
     * @param int $serviceId System identifier of account
     * @return string
     */
    public function readBaseDomain(int $serviceId): string
    {
        try {
            if (!file_exists(self::TOKENS_FILE)) {
                throw new Exception('Tokens file not found.');
            }

            $credentials = json_decode(file_get_contents(self::TOKENS_FILE), true);
            if (empty($credentials[$serviceId])) {
                throw new Exception("Unknown account name \"$serviceId\".");
            }

            if (empty($credentials[$serviceId]['base_domain'])) {
                throw new Exception("Something went wrong on the server");
            };

            return $credentials[$serviceId]['base_domain'];
        } catch (Throwable $e) {
            exit($e->getMessage());
        }
    }

    /**
     * Setting up mandatory credentials
     *
     * @param int $serviceId
     * @return $this
     */
    public function setUpMandatoryCredentials(int $serviceId): self
    {
        $accessToken = $this->readToken($serviceId);
        $baseDomain = $this->readBaseDomain($serviceId);

        $this->apiClient->setAccessToken($accessToken);
        $this->apiClient->setAccountBaseDomain($baseDomain);

        return $this;
    }

    /**
     * Setting up mandatory credentials manually
     * 
     * @param string $baseDomain
     * @param string $accessToken
     * @param string $refreshToken
     * @param int $expires
     * 
     * @return self
     */
    public function setUpMandatoryCredentialsManually(
        string $baseDomain,
        string $accessToken,
        string $refreshToken,
        int $expires
    ): self
    {
        $accessTokenObject = new AccessToken([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires' => $expires
        ]);

        $this->apiClient->setAccessToken($accessTokenObject);
        $this->apiClient->setAccountBaseDomain($baseDomain);

        return $this;
    }

    /**
     * Get new tokens
     * 
     * @param string $accessToken
     * @param string $refreshToken
     * @param int $expires
     * @param string $baseDomain
     * 
     * @return AccessTokenInterface
     */
    public function getNewTokenByRefreshToken(
        string $accessToken,
        string $refreshToken,
        int $expires,
        string $baseDomain 
    ): AccessTokenInterface
    {
        $this->apiClient->setAccountBaseDomain($baseDomain);
        $accessTokenObject = new AccessToken([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires' => $expires
        ]);
        $newAccessTokenObject = $this->apiClient
                                ->getOAuthClient()
                                ->getAccessTokenByRefreshToken($accessTokenObject);
        
        return $newAccessTokenObject;
    }

    /**
     * Getting all contacts
     *
     * @return array
     */
    public function getContacts(): array
    {
        $contacts = $this->apiClient->contacts()->get()->toArray();
        return $contacts;
    }

    /**
     * Getting contacts with pagination
     * 
     * @param $page
     * @return ContactsCollection
     */
    public function getContactsPage(int $page): ContactsCollection 
    {
        $pagination = new PagesFilter();
        $pagination->setLimit(50);
        $pagination->setPage($page);

        $contacts = $this->apiClient->contacts()->get($pagination);
        
        return $contacts;
    }

    /**
     * Getting account information as array
     * 
     * @return array 
     */
    public function getAccountInformation(): array
    {
        $accountData = $this
                        ->apiClient
                        ->account()
                        ->getCurrent()
                        ->toArray();
        return $accountData;
    }

    /**
     * Subscribing to hook
     * 
     * @return
     */
    public function subscribeToWebHook($webHookModel): array
    {
        $response = $this->apiClient
            ->webhooks()
            ->subscribe($webHookModel)
            ->toArray();
        
        return $response;
    }

}
