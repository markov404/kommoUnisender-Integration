<?php

namespace Managers;

use Abstractions\AbsModelManager;
use Abstractions\AbsException;

use InvalidArgumentException;
use Models\Accesses;
use Exception;
use Throwable;


/**
 * Class AccessesManager
 * 
 * Managing requests to accesses table..
 * Created for encapsulation of infrastructure layer of this app.
 * 
 * @package Managers\AccessesManager
 */
class AccessesManager extends AbsModelManager
{
    /**
     * This method is creating new integration record.
     * 
     * @param array $data
     * @return object
     */
    public function create(array $data): object
    {  
        if (!$this->validateInputFields($data, $this->fillableFields)) {
            $e = new AbsException(new InvalidArgumentException(
                'While creating new record via accesses model' . 
                ' you should provide an associative array with keys: [' .
                implode(',', $this->fillableFields) . ']'));
            return $e;
        }

        if (!((new $this->model() instanceof Accesses))) {
            throw new Exception('You should pass an 
            Accesses::class to constructor of this manager!');
        }
   
        try {
            $response = parent::create($data);
        } catch(Throwable $e) {
            $response = new AbsException($e);
        }

        return $response;
    }

    /**
     * Validation
     * 
     * @return bool
     */
    private function validateInputFields(array $fields, array $fieldsRequested): bool
    {
        $flag = true;
        foreach ($fieldsRequested as $requestedFields) {
            if (!array_key_exists($requestedFields, $fields)) {
                $flag = false;
            }
        }

        return $flag;
    }
}