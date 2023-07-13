<?php

namespace Interfaces;

/**
 * Interface ExceptionInterface.
 * Interface for abstract Exception.
 *
 * @package Interfaces\ExceptionInterface
 */
interface ExceptionInterface 
{
    /**
     * Json (json alike array) representation of exception
     * @return string
     */
    public function asPhpJson(): string;
}