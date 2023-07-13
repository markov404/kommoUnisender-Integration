<?php

namespace Managers;

use Exception;
use Interfaces\AccountsManagerInterface;
use Abstractions\AbsModelManager;
use Abstractions\AbsException;

use Throwable;


/**
 * Class AccountsManager extending AbsModelManager
 * 
 * Managing requests to accounts table..
 * Created for encapsulation of infrastructure layer of this app.
 * 
 * @package Managers\AccountsManager
 */
class AccountsManager extends AbsModelManager implements AccountsManagerInterface
{
    /**
     * Getting account accesses
     * @param string $query
     * @return object
     */
    public function getAccountAccessesWithWhere(string $query): object
    {
        try {
            $accounts = $this->model::whereRaw($query)->get();
            if ($accounts->count() == 1) {
                $response = $accounts[0]->access()->get()->first();
            } else {
                throw new Exception(
                    'Somehow we have 2 accounts' . 
                    ' with same accounts_kommo_id ' .
                    ',that means that consistancy of our data is damaged.');
            }
        } catch (Throwable $e) {
            $response = new AbsException($e);
        }

        return $response;
    }
}