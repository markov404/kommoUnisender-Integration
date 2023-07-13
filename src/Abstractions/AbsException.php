<?php

namespace Abstractions;

use Throwable;
use Interfaces\ExceptionInterface;

/**
 * Class AbsResponse.
 *
 * Abstract implementation of ExceptionInterface.
 * To storing and tranfering exceptions in this object.
 *
 * @package Abstractions\AbsException
 * @author mmarkov mmarkov@team.amocrm.com
 */
class AbsException implements ExceptionInterface
{
    /**
     * Exception message
     * @var string $message
     */
    protected string $message;

    /**
     * Exception type
     * @var string $typeOf
     */
    protected string $typeOf;

    /**
     * Actual exception
     * @var Throwable $exception
     */
    private Throwable $exception;

    /**
     * Constructor
     * @param Throwable $e exception
     */
    public function __construct(Throwable $e)
    {
        $this->message = $e->getMessage();
        $this->typeOf = get_class($e);
        $this->exception = $e;
    }

    /**
     * Representing of exception as string (json alike).
     * @return string
     */
    public function asPhpJson(): string
    {
        $jsonAlikeArray = array(
            'GetExceptionTypeOf' => $this->typeOf,
            'With message' => $this->message
        );

        return json_encode($jsonAlikeArray);
    }

    /**
     * Using of this shit is your responsibility...
     * 
     * @return Throwable
     */
    public function getException(): Throwable
    {
        return $this->exception;
    }
}