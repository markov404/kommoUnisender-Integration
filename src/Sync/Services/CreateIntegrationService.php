<?php

namespace Sync\Services;

use Interfaces\LoggerInterface;
use Abstractions\Types\SetOfModelManagers;
use Abstractions\AbsService;

use Models\Integrations;


/**
 * Class CreateIntegrationService.
 *
 * Extending AbsService.
 * Managing adding integration into db.
 *
 * @package Sync\Services\CreateIntegrationService
 */
class CreateIntegrationService extends AbsService
{
    /**
     *
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param SetOfModelManagers $modelManagers
     */
    public function __construct(
        LoggerInterface $logger = null,
        SetOfModelManagers $modelManagers = null)
    {
        parent::__construct($logger, $modelManagers);
    }

    /**
     *
     * Дергать его отсюда.
     *
     * @param array $dataList input data.
     * @return void
     */
    public function execute(array $dataList = array()): void
    {
        parent::execute($dataList);

        /** Validating request parameters */
        if (!$this->validateRequestParams(
            $dataList, [
                'integrations_secret_key', 
                'integrations_redirect_url', 
                'integrations_id', 
                'integrations_domain']
                )
            ) {
            $this->makeResponseObject([
                "info" => "Invalid request parameters..."
            ], 400);
            return;
        }

        // /** @var ModelManagerInterface $integrationsManager */
        // $integrationsManager = $this->setOfModelManagers->getManager('integrations');

        // $response = $integrationsManager->getFirst();


        /**
         * If we already saved integrations we are not allowing
         * to save another, it can be skipped if we want.
         */
        $isThereAnIntegration = (Integrations::all()->count() == 1);

        if ($isThereAnIntegration) {
            $this->makeResponseObject([
                'info' => 'There is an integration, already.'
            ], 400);
            return;
        }

        $response = Integrations::create($dataList);

        //$response = $integrationsManager->create($dataList);

        /** Checking if response is Bad */
        // if (Utils::isCustomException($response)) {
        //     $this->writeErrorLogLine($response->asPhpJson());
        //     $this->makeResponseObject([
        //         'info' => 'Something went wrong while creating new integration',
        //     ], 500);
        //     return;
        // }

        $this->makeResponseObject(
            array_merge($response->toArray(), [
                "info" => "Your integrations is saved."
            ]), 
            200
        );
        return;
    }
}
