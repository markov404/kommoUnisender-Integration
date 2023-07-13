<?php

namespace Managers;

use Abstractions\AbsModelManager;
use Abstractions\AbsException;

use Throwable;
use InvalidArgumentException;
use Exception;
use Models\Integrations;


/**
 * Class IntegrationsManager
 * 
 * Managing requests to integrations table..
 * Created for encapsulation of infrastructure layer of this app.
 * 
 * @package Managers\IntegrationsManager
 */
class IntegrationsManager extends AbsModelManager
{
    /**
     * This method is creating new integration record.
     * 
     * @param array $data
     * @return object
     */
    public function create(array $data): object
    {  
        if (!$this->validateInputFields($data, $this->fillableFields)) {
            $e = new AbsException(new InvalidArgumentException(
                'While creating new record via intagration model' . 
                ' you should provide an associative array with keys: [' .
                implode(',', $this->fillableFields) . ']'));
            return $e;
        }

        if (!((new $this->model() instanceof Integrations))) {
            throw new Exception('You should pass an 
            Integrations::class to constructor of this manager!');
        }

        $modelResponse = Integrations::find($data['integrations_secret_key']);
        if (!is_null($modelResponse)) {
            return $modelResponse;
        } else {
            return parent::create($data);
        };
    }

    /**
     * Utility method for getting integration data.
     * 
     * @return object
     */
    public function getFirstIntegration(): object
    {
        try {
            $response = Integrations::all()->first();
        } catch (Throwable $e) {
            $response = new AbsException($e);
        }

        return $response;
    }

    /**
     * Returning an associative array of integration data
     * 
     * @return ?array
     */
    function getIntegrationDataAsArray(): ?array
    {
        $amountOfIntegrations = Integrations::all()->count();
        if ($amountOfIntegrations < 1) {
            return null;
        }

        try {
            $integration = Integrations::all()->first();
            return array (
                'secret_key' => $integration->integrations_secret_key,
                'redirect_url' => $integration->integrations_redirect_url,
                'id' => $integration->integrations_id
            );
        } catch (Throwable $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    /**
     * Utility method for checking is there an integration with specifi id
     * @param int $id
     * @return object
     */
    public function isThereAnIntegrationWithId(int $id): object
    {
        try {
            $response = Integrations::find($id);
        } catch (Throwable $e) {
            $response = new AbsException($e);
        }

        return $response;
    }

    /**
     * Utility method for getting first integration_id.
     * 
     * @return string
     */
    public function getFisrstIntegrationId(): string
    {
        try {
            $response = Integrations::all('integrations_id')->first();
            $response = $response['integrations_id'];
        } catch (Throwable $e) {
            $response = new AbsException($e);
        }

        return $response;
    }

    /**
     * Validation
     * 
     * @return bool
     */
    private function validateInputFields(array $fields, array $fieldsRequested): bool
    {
        $flag = true;
        foreach ($fieldsRequested as $requestedFields) {
            if (!array_key_exists($requestedFields, $fields)) {
                $flag = false;
            }
        }

        return $flag;
    }
}