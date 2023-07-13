<?php

declare(strict_types=1);

namespace Sync;

/**
 * The configuration provider
 *
 * @see https://docs.laminas.dev/laminas-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'users' => $this->getConfig(),
            'laminas-cli' => $this->getCliConfig(),
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies(): array
    {
        return [
            'invokables' => [
                Sync\Handlers\AuthHandler::class => Sync\Handler\AuthCallbackHandler::class,
            ],
            'factories'  => [
                Sync\Handlers\CreateIntegrationHandler::class => Sync\Factories\CreateIntegrationHandlerFactory::class,
                Sync\Handlers\PingUnisenderHandler::class => Sync\Factories\PingUnisenderHandlerFactory::class, // Not mandatory
                Sync\Handlers\ContactsHandler::class => Sync\Factories\ContactsHandlerFactory::class, // Not mandatory
                Sync\Handlers\AuthCallbackHandler::class => Sync\Factories\AuthCallbackHandlerFactory::class,
                Handlers\SumHandler::class => Factories\SumHandlerFactory::class,
            ],
        ];
    }

    /**
     * Returns the container dependencies for cli
     */
    public function getCliConfig(): array  
    {
        return [
            'commands' => [
                'sync:how-time' => Commands\HowTime::class,
                'sync:update-accesses' => Commands\UpdateAccesses::class
            ]
        ];
    }

    public function getConfig(): array
    {
        return [
            'paths' => [
                'enable_registration' => true,
                'enable_username'     => false,
                'enable_display_name' => true,
            ],
        ];
    }
}
