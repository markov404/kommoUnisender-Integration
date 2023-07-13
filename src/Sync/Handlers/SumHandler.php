<?php

declare(strict_types=1);

namespace Sync\Handlers;


use Sync\Services\SumService;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class SumHandler
 * Implementation of RequestHandlerInterface
 *
 * Processing summing functionality... kinda of test endpoint.
 *
 * @package Sync\Handlers\SumHandler
 * @author mmarkov mmarkov@team.amocrm.com
 */
class SumHandler implements RequestHandlerInterface
{
    /**
     * @var SumService Contains service object
     */
    private SumService $sumService;

    /**
     * Constructor
     *
     * @param SumService $service
     */
    public function __construct(SumService $service)
    {
        $this->sumService = $service;
    }

    /**
     * Handler endpoint
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->sumService->execute($request->getQueryParams());
        $response = $this->sumService->getResponse();

        return $response->asJson();
    }
}
