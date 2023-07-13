<?php

namespace Interfaces;

use Interfaces\ModelManagerInterface;

/**
 * Interface for AccountsManagerInterface
 */
interface AccountsManagerInterface extends ModelManagerInterface 
{
    /**
     * Getting account accesses
     * @param string $query
     * @return object
     */
    public function getAccountAccessesWithWhere(string $query): object;
}
