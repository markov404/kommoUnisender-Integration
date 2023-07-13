<?php

namespace Sync\Services;

use Interfaces\LoggerInterface;
use Abstractions\AbsService;
use Models\Accounts;

use Unisender\ApiWrapper\UnisenderApi;
use Utils\Utils;


/**
 * Class GetContactFromUnisenderService.
 *
 * Extending AbsService.
 * Managing getting contacts form Unisender.
 *
 * @package Sync\Services\GetContactFromUnisenderService
 */
class GetContactFromUnisenderService extends AbsService
{
    /**
     *
     * Constructor
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        parent::__construct($logger);
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
        Utils::bootEloquent();
        
        
        if (!$this->validateRequestParams($dataList, ['email', 'id'])) {
            $this->makeResponseObject([null], 400);
            return;
        }

        $account = Accounts::whereAccountsKommoId($dataList['id'])->get()->first();
        if (is_null($account)) {
            $this->makeResponseObject([
                'info' => 'You are not authorised.'
            ], 401);
            return;
        }

        $apiKey = $account->unisender_key;


        $email = $dataList['email'];

        $unisenderApi = new UnisenderApi($apiKey);
        $response = $unisenderApi->getContact(['email' => $email]);

        $data = json_decode($response, true);

        $this->makeResponseObject([
            $data
        ], 200);
    }
}
