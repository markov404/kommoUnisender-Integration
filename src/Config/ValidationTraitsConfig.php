<?php

namespace Config;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class ValidationTraitsConfig.
 * 
 * @package Config\ValidationTraitsConfig
 */
class ValidationTraitsConfig
{
    /** @var array $listOfTraits */
    public array $listOfTraits;

    /**
     * Constructor ValidationTraitsConfig
     * 
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        try {
            $listOfTraits = $container->get('config')['validation_traits'];
            $this->listOfTraits = $listOfTraits;
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
            exit($e->getMessage());
        }
    }

    /**
     * Returning an connection
     * 
     * @return ?array
     */
    public function getTraits(): ?array
    {
        return $this->listOfTraits;
    }
}