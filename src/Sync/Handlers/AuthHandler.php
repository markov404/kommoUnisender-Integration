<?php

declare(strict_types=1);

namespace Sync\Handlers;

use Laminas\Diactoros\Response\JsonResponse;

use Utils\Utils;
use Sdk\AmoApiClient;

use Models\Integrations;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class AuthHandler
 * Implementation of RequestHandlerInterface
 * Startpoint of authorisation process
 * 
 * INVOKABLE
 * 
 * @package Sync\Handlers\AuthHandler
 * @author mmarkov mmarkov@team.amocrm.com
 */
class AuthHandler implements RequestHandlerInterface
{
    /**
     * Handler endpoint
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** Checking if we have an id in query parameters. */
        $queryParams = $request->getQueryParams();

        /** Checking on valid request parameters. */
        $shouldBeNull =$this->validateParametersAndMakeCorrespondingResponse($queryParams);
        if (!is_null($shouldBeNull)) {
            return $shouldBeNull;
        }

        /** Loading all that we need */
        Utils::loadEnvIfNotloadedAlready();
        Utils::bootEloquent();
        $integration = Integrations::all()->first();
        $domain = $_ENV['INTEGRATION_DOMAIN'];

        /** Checking on existing integration in db state. */
        $sholdBeNull = $this->checkIfIntegrationIsSettedUpAndMakeCorrespondingResponse(
            $integration, $domain);
        if (!is_null($sholdBeNull)) {
            return $sholdBeNull;
        }

        /** Creating new AmoAPIClient */
        $client = new AmoApiClient(
            $integration->integrations_id,
            $integration->integrations_secret_key,
            $integration->integrations_redirect_url
        );

        /** Redirecting to allowing credentials page. */
        $client->auth($queryParams);

        return new JsonResponse([
            'status' => 'success',
            'data' => [null],
            'message' => 'OK',
            'code' => 200
        ]);
    }

    /** 
     * Just for more better looking.
     * 
     * @param array $data 
     * @return ?JsonResponse
     */
    public function validateParametersAndMakeCorrespondingResponse($data): ?JsonResponse
    {
        $correspondingResponse = [
            'status' => 'error',
            'data' => [null],
            'message' => 'The server cannot or will not process' .
            ' the request due to something that is perceived to be a client error',
            'code' => 400
        ];

        $messageTwo = 'Account id should be numeric';

        if (!array_key_exists('id', $data)) {
            return new JsonResponse($correspondingResponse);
        } else if ($data['id'] == '') {
            return new JsonResponse($correspondingResponse);
        } else if (!is_numeric($data['id'])) {
            $correspondingResponse['message'] = $messageTwo;
            return new JsonResponse($correspondingResponse);
        } else {
            return null;
        }
    }

    /** 
     * Just for more better looking
     * 
     * @param Integrations $integration
     * @param string $domain
     * @return ?JsonResponse
     */
    public function checkIfIntegrationIsSettedUpAndMakeCorrespondingResponse(
        Integrations $integration,
        string $domain): ?JsonResponse
    {
        if (is_null($integration)) {
            return new JsonResponse([
                'status' => 'error',
                'data' => ["<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n
                <D:error xmlns:D=\"DAV:\">\n
                  <D:lock-token-submitted>\n
                    <D:href>https://{$domain}/*</D:href>\n
                  </D:lock-token-submitted>\n
                </D:error>\n"],
                'info' => 'The integration data has not been setted up yet, API is not ready.',
                'message' => 'The resource that is being accessed is locked.',
                'code' => 423
            ]);
        } else {
            return null;
        }
    }
}
