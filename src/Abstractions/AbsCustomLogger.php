<?php

namespace Abstractions;

use Interfaces\LoggerInterface;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * class AbsCustomLogger
 *
 * AbsCustomLogger is implementation of LoggerInterface,
 * BASE class of CustomLogger.
 *
 * @package Abstractions\AbsCustomLogger
 * @author mmarkov mmarkov@team.amocrm.com
 */
class AbsCustomLogger implements LoggerInterface
{
    /** @var string $name Name of logger */
    private $name;

    /** @var string $path Working path of logger */
    private $path;

    /** @var object $usedBy Class name that use it */
    private $usedBy;

    /** @var Logger $logger Object of Monolog\Logger */
    private $logger;

    /**
     * Constructor
     *
     * @param object $usedBy Pass here object which uses this logger!
     * @param string $loggingRootPath Pass here root folder of logs.
     * @param string $currentDate Pass here current date for logging.
     */
    public function __construct(
        object $usedBy = null,
        string $loggingRootPath = 'logs/',
        string $currentDate = '00-00-00'
    )
    {
        is_null($usedBy) ? $this->usedBy = "Global" : $this->usedBy = get_class($usedBy);
        $this->name = "$this->usedBy . Logger"; // as example: GlobalLogger

        $this->path = $loggingRootPath . $currentDate . "/request.log";
        $this->logger = new Logger($this->name);
        $this->logger->pushHandler(new StreamHandler($this->path, Logger::DEBUG));
    }

    /**
     * Should write log line with status INFO
     *
     * @param string $message some text-message for logging
     * @return $this
     */
    public function info(string $message): self
    {
        $this->logger->info($message);

        return $this;
    }

    /**
     * Should write log line with status WARNING
     *
     * @param string $message some text-message for logging
     * @return $this
     */
    public function warning(string $message): self
    {
        $this->logger->warning($message);

        return $this;
    }

    /**
     * Should write log line with status ERROR
     *
     * @param string $message some text-message for logging
     * @return $this
     */
    public function error(string $message): self
    {
        $this->logger->error($message);

        return $this;
    }

    /**
     * Should write log line with status DEBUG
     * 
     * @param string $mesasge some text-message for logging
     * @return $this
     */
    public function debug(string $mesasge): self 
    {
        $this->logger->debug($mesasge);

        return $this;
    }
}
