<?php

namespace Sync\Services;

use Interfaces\LoggerInterface;
use Abstractions\AbsService;
use Models\Accounts;
use Unisender\ApiWrapper\UnisenderApi;
use Utils\Utils;


/**
 * Class PingUnisenderService.
 *
 * Extending AbsService.
 * Managing Unisender testing.
 *
 * @package Sync\Services\PingUnisenderService
 */
class PingUnisenderService extends AbsService
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

        if (array_key_exists('delete', $dataList)) {
            if ($dataList['delete'] === '') {
                $this->makeResponseObject([null], 400);
                return;
            } else {
                $deleteFlag = $dataList['delete'];
            };

        } else {
            $deleteFlag = '0';
        }

        $emails = $dataList['email'];
        $pushData = array();
 
        for ($i = 0; $i < count($emails); $i++) {
           $pushData[$i] = array($emails[$i], $deleteFlag);
        }

        $unisenderApi = new UnisenderApi($apiKey);
        $response = $unisenderApi->importContacts([
            "field_names" => ['email', 'delete'], 
            "data" => $pushData
        ]);

        $data = json_decode($response, true);

        $this->makeResponseObject([
            $data
        ], 200);
    }
}
