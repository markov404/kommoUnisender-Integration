<?php

namespace Abstractions;

use Exception;
use InvalidArgumentException;
use Throwable;
use Interfaces\ModelManagerInterface;
use Abstractions\AbsException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Utils\Utils;

class DTO
{
    public array $data;
    
    public function __construct($data)
    {
        $this->data = $data;
    }
}

/**
 * class AbsModelManager
 *
 * AbsModelManager is implementation of ModelManagerInterface,
 * BASE class of all ModelManagers.
 *
 * @package Abstractions\AbsModelManager
 * @author mmarkov mmarkov@team.amocrm.com
 */
class AbsModelManager implements ModelManagerInterface
{
    /**
     * All fillable fields of model related tos this manager.
     * @var array $fillableFields
     */
    protected array $fillableFields;

    /**
     * Model name which this manager is working off.
     * @var string $model
     */
    protected string $model;

    /**
     * Constructor
     *
     * @param string $model Model which this manager will working off.
     * @param array $connectionConfig An array of data for connection.
     */
    public function __construct(
        string $model,
        array $connectionConfig = null
    ) {
        // Validating if $mode is actually Model...
        if (!((new $model()) instanceof Model)) {
            throw new Exception("You should pass an instance of Model to manager");
        }

        $this->model = $model; // Our model...
        $this->fillableFields = $this->model::getFillableColumns(); // Getting fields array

        Utils::bootEloquent($connectionConfig); // Booting connection
    }

    /**
     * Base implementation of create method
     *
     * @param array $data An associative array
     * with structure (%column% => %data%)
     * @return object
     */
    public function create(array $data): object
    {
        if (!$this->validateInputFields($data, $this->fillableFields)) {
            $e = new AbsException(new InvalidArgumentException(
                'While creating new record via '. $this->model . ' manager' . 
                ' you should provide an associative array with only this set of keys: [' .
                implode(',', $this->fillableFields) . ']'));
            return $e;
        }

        try {
            $instance = $this->model::create($data);
        } catch (Throwable $e) {
            $instance = new AbsException($e);
        }

        return $instance;
    }

    /**
     * Base implementation of createMany method
     * 
     * @param array $data ARRAY OF ASSOCIATIVE ARRAYS
     * @return object 
     */
    public function insertMany(array $data): object
    {
        foreach ($data as $item) {
            if (!$this->validateInputFields($item, $this->fillableFields)) {
                $e = new AbsException(new InvalidArgumentException(
                    'While creating new record via '. $this->model . ' manager' . 
                    ' you should provide an associative array with only this set of keys: [' .
                    implode(',', $this->fillableFields) . ']'));
                return $e;
            }
        }

        try {   

            $amount = count($data);
            foreach ($data as $item) 
            {
                $lastId = $result = $this->model::insertGetId($item);
            }
            $startId = ($lastId - $amount) + 1;

            $output = array();
            for ($startId; $startId <= $lastId; $startId++) {
                array_push($output, $startId);
            }
            $result = new DTO($output);

        } catch (Throwable $e) {
            $result = new AbsException($e);
        }

        return $result;
    }

    /**
     * Getting all entities
     * @return object
     */
    public function getAll(): object
    {
        try {
            $result = $this->model::all();
        } catch(Throwable $e) {
            $result = new AbsException($e);
        }

        return $result;
    }

    /**
     * Getting entity with specific id
     * @param mixed $id
     * @return object
     */
    public function entityWithId($primaryKey): object
    {
        try {
            $instance = $this->model::find($primaryKey);
        } catch (Throwable $e) {
            $instance = new AbsException($e);
        }

        return $instance;
    }

    /**
     * Is there an entity or set of entities with
     * specific fields equation
     * @param string $query
     * @return object
     */
    public function entityWhere(string $query): object
    {
        try {
            $instance = $this->model::whereRaw($query)->get();
        } catch (Throwable $e) {
            $instance = new AbsException($e);
        }

        return $instance;
    }

    /**
     * Getting first of all
     * @return object
     */
    public function getFirst(): object
    {
        try {
            $instance = $this->model::all()->first();
        } catch (Throwable $e) {
            $instance = new AbsException($e);
        }

        return $instance;
    }
    
    /**
     * Delete first with
     * 
     * @param string $query 
     * @return object
     */
    public function deleteFirstWith(string $query): object
    {
        try {
            $result = $this->model::where($query)->get()[0]->delete();
        } catch (Throwable $e) {
            $result = new AbsException($e);
        }

        return $result;
    }

    /**
     * Validation of input fields
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
