<?php

namespace Interfaces;

use Laminas\Diactoros\Response\JsonResponse;

/**
 * Interface ResponseInterface.
 *
 * Предпологается что итогом его реализации должен
 * являтся DTO для получения результата HttpHandler`ом от ServiceInterface.
 *
 * @package Interfaces\ResponseInterface
 */
interface ResponseInterface
{
    /**
     * Returns response as Json
     *
     * @return JsonResponse
     */
    public function asJson(): JsonResponse;

    /**
     * Returns response as associative array
     *
     * @return array
     */
    public function asDictionary(): array;

    /**
     * Returns response as array
     *
     * @return array
     */
    public function asList(): array;
}
