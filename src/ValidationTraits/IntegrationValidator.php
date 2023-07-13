<?php

namespace Traits;

use Managers\IntegrationsManager;
use Models\Integrations;

/**
 * trait IntegrationValidator
 * 
 * @package Traits\IntegrationValidator
 */
trait IntegrationValidator 
{
    /** @var static string $integrationValidatorMessage */
    public static string $integrationValidatorMessage = 'There is no integration is setted up yet!';

    /**
     * Validating if integration is setted up
     * @return bool
     */
    public static function validateIntegrationStatus(): bool
    {
        $integrationManager = new IntegrationsManager(Integrations::class);
        $integration = $integrationManager->getIntegrationDataAsArray();

        if (is_null($integration)) {
            return false;
        }
        return true;
    }
}