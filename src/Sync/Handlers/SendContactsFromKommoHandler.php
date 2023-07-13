<?php

declare(strict_types=1);

namespace Sync\Handlers;

use Interfaces\ServiceInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class SendContactsFromKommoHandler
 * Implementation of RequestHandlerInterface
 *
 * Getting contacts from Kommo and send it to Unisender
 *
 * @package Sync\Handlers\SendContactsFromKommoHandler
 */
class SendContactsFromKommoHandler implements RequestHandlerInterface
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
        $data = json_decode($request->getBody()->read(1024), true);
        $this->clientsService->execute($data);

        $response = $this->clientsService->getResponse();
        return $response->asJson();
    }
}
