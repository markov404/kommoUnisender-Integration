<?php

namespace Interfaces;


/**
 * Interface ModelManagerInterface.
 * Interface for model manager.
 *
 * @package Interfaces\ModelManagerInterface
 */
interface ModelManagerInterface
{
    /**
     * Creating new record in table
     * Should return an instance of model
     * @param array $data
     */
    public function create(array $data): object;

    /**
     * Creating new MANY recordS in table
     * @param array $data ARRAY OF ASSOCIATIVE ARRAYS
     * @return object
     */
    public function insertMany(array $data): object;

    /**
     * Getting all entities
     * @return object
     */
    public function getAll(): object;

    /**
     * Getting entity with specific id
     * @param mixed $id
     * @return object
     */
    public function entityWithId($primaryKey): object;

    /**
     * Is there an entity or set of entities with
     * specific fields equation
     * @param string $query
     * @return object
     */
    public function entityWhere(string $query): object;

    /**
     * Delete first with
     * 
     * @param string $query 
     * @return object
     */
    public function deleteFirstWith(string $query): object;

    /**
     * Getting first of all
     * @return object
     */
    public function getFirst(): object;
}
