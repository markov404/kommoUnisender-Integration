<?php

declare(strict_types=1);

namespace Sync\Handlers;

use Interfaces\ServiceInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class GetContactFromUnisenderHandler
 * Implementation of RequestHandlerInterface
 *
 * Getting contact information via email from Unisender.
 *
 * @package Sync\Handlers\GetContactFromUnisenderHandler
 */
class GetContactFromUnisenderHandler implements RequestHandlerInterface
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
