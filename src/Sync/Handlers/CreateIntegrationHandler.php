<?php

declare(strict_types=1);

namespace Sync\Handlers;

use Interfaces\ServiceInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class CreateIntegrationHandler
 * Implementation of RequestHandlerInterface
 *
 * Adding new integration to database.
 *
 * @package Sync\Handlers\CreateIntegrationHandler
 */
class CreateIntegrationHandler implements RequestHandlerInterface
{
    /**
     * @var ServiceInterface содержит сервис для исполнения задачи.
     */
    private ServiceInterface $clientsService;

    /**
     * Constructor
     *
     * @param ServiceInterface $service
     */
    public function __construct(ServiceInterface $service)
    {
        $this->clientsService = $service;
    }

    /**
     * Handler endpoint
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $urlParams = $request->getQueryParams();
        $this->clientsService->execute($urlParams);

        return $this->clientsService->getResponse()->asJson();
    }
}
