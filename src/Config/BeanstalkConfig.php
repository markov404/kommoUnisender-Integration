<?php

namespace Config;

use Pheanstalk\Pheanstalk;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class BeanstalkConfig.
 * 
 * @package Sync\config
 */
class BeanstalkConfig
{
    /** @var Pheanstalk|null $connection */
    private ?Pheanstalk $connection;

    /** @var string $host */
    public string $host;

    /** @var string $port */
    public string $port;

    /** @var string $timeout */
    public string $timeout;

    /**
     * Constructor BeanstalkConfig
     * 
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        try {
            $config = $container->get('config')['beanstalk'];
            
            $this->host = $config['host'];
            $this->port = $config['port'];
            $this->timeout = $config['timeout'];

            $this->connection = Pheanstalk::create(
                $config['host'],
                $config['port'],
                $config['timeout']
            );
        } catch (NotFoundExceptionInterface | ContainerExceptionInterface $e) {
            exit($e->getMessage());
        }
    }

    /**
     * Returning an connection
     * 
     * @return Pheanstalk|null
     */
    public function getConnection(): ?Pheanstalk
    {
        return $this->connection;
    }
}