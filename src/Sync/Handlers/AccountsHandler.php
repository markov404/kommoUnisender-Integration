<?php

declare(strict_types=1);

namespace Sync\Handlers;

use Interfaces\ServiceInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class AccountsHandler
 * Implementation of RequestHandlerInterface
 * Processing callback from kommo API
 *
 * @package Sync\Handlers\AccountsHandler
 */
class AccountsHandler implements RequestHandlerInterface
{
    /**
     * @var ServiceInterface содержит сервис для исполнения задачи.
     */
    private ServiceInterface $callBackService;

    /**
     * Constructor
     *
     * @param ServiceInterface $service
     */
    public function __construct(ServiceInterface $service)
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
        // Create and return a response
        $query = $request->getQueryParams();
        $this->callBackService->execute($query);

        return $this->callBackService->getResponse()->asJson();
    }
}
