<?php

namespace Abstractions\Types;

use Exception;
use Interfaces\ModelManagerInterface;


/**
 * Class SetOfModelManagers
 * 
 * @package Abstractions\Types\SetOfModelManagers
 */
class SetOfModelManagers 
{
    /** @var array $list for storing ModelManagers */
    private array $list;

    /** @var array $keys list of keys */
    private array $keys;

    /**
     * Constructor
     * 
     */
    public function __construct() 
    {
        $this->list = array(); // Initialise list of Managers
        $this->keys = array(); // Initialise list of keys
    }

    /** 
     * Adding new item 
     * @param string $name Key to storing this manager
     * @param ModelManagerInterface $modelManager
     * @return void
     * */
    public function addManagerToList(
        string $name, ModelManagerInterface $modelManager): void 
    {
        array_push($this->keys, $name);
        $this->list[$name] = $modelManager;
    }

    /**
     * Getting manager by key
     * @param string $name
     * @return ModelManagerInterface
     */
    public function getManager(string $name): ModelManagerInterface
    {
        if (!array_key_exists($name, $this->list)) {
            throw new Exception('There is not such manager in list...');
        }
        return $this->list[$name];
    }
}