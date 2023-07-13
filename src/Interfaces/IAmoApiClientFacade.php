<?php

namespace Interfaces;

use AmoCRM\Collections\ContactsCollection;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Interface for amoclient facade
 * 
 * @package Interfaces\AmoApiClient
 * @author mmarkov mmarkov@team.amocrm.com
 */
interface AmoApiClientFacade 
{
    /**
     * Получение токена досутпа для аккаунта.
     *
     * @param array $queryParams Входные GET параметры.
     * @param &$accessesContainer Container to put in accesses data.
     * @return string Имя авторизованного аккаунта.
     */
    public function auth(
        array $queryParams, 
        array &$accessesContainer = null): string;

    /**
     * Получение токена из файла.
     *
     * @param int $serviceId Системный идентификатор аккаунта.
     * @return AccessToken
     */
    public function readToken(int $serviceId): AccessToken;

    /**
     * Get BaseDomain from file
     *
     * @param int $serviceId System identifier of account
     * @return string
     */
    public function readBaseDomain(int $serviceId): string;

    /**
     * Setting up mandatory credentials
     *
     * @param int $serviceId
     * @return $this
     */
    public function setUpMandatoryCredentials(int $serviceId): self;

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
    ): self;

    /**
     * Getting all contacts
     *
     * @return array
     */
    public function getContacts(): array;

    /**
     * Getting contacts with pagination
     * 
     * @param $page
     * @return ContactsCollection
     */
    public function getContactsPage(int $page): ContactsCollection;

    /**
     * Getting account information as array
     * 
     * @return array 
     */
    public function getAccountInformation(): array;

    /**
     * Subscribing to hook
     * 
     * @return
     */
    public function subscribeToWebHook($webHookModel): array;
}