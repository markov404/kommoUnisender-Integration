<?php

declare(strict_types=1);

namespace Sync\Handlers;

use Sync\Services\AuthCallbackService;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class AuthCallbackHandler
 * Implementation of RequestHandlerInterface
 * Processing callback from kommo API
 *
 * @package Sync\Handlers\AuthCallbackHandler
 */
class AuthCallbackHandler implements RequestHandlerInterface
{
    /**
     * @var AuthCallbackService содержит сервис для исполнения задачи.
     */
    private AuthCallbackService $callBackService;

    /**
     * Constructor
     *
     * @param AuthCallbackService $service
     */
    public function __construct(AuthCallbackService $service)
    {
        $this->callBackService = $service;
    }

    /**
     * Handling request
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {

        $query = $request->getQueryParams();
        $this->callBackService->execute($query);

        return $this->callBackService->getResponse()->asJson();
    }
}
