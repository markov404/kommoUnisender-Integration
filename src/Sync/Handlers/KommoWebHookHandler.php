<?php

declare(strict_types=1);

namespace Sync\Handlers;

use Sync\Services\KommoWebHookService;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class KommoWebHookHandler
 * Implementation of RequestHandlerInterface
 *
 * Processing webhooks from Kommo app
 *
 * @package Sync\Handlers\KommoWebHookHandler
 */
class KommoWebHookHandler implements RequestHandlerInterface
{
    /**
     * @var KommoWebHookService содержит сервис для исполнения задачи.
     */
    private KommoWebHookService $clientsService;

    /**
     * Constructor
     *
     * @param KommoWebHookService $service
     */
    public function __construct(KommoWebHookService $service)
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
        error_log('hui');
        $urlParams = $request->getParsedBody();

        $this->clientsService->execute($urlParams);

        $response = $this->clientsService->getResponse();
        return $response->asJson();
    }
}
