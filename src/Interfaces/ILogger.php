<?php

namespace Interfaces;

/**
 * interface LoggerInterface
 *
 * Implementation of this interface shoulde be class which
 * responsibility is logging to certaint path/file.
 *
 * @package Interfaces\LoggerInterface
 */
interface LoggerInterface
{
    /**
     * Should write log line with status INFO
     *
     * @param string $message some text
     */
    public function info(string $message);

    /**
     * Should write log line with status WARNING
     *
     * @param string $message some text
     */
    public function warning(string $message);

    /**
     * Should write log line with status ERROR
     *
     * @param string $message some text
     */
    public function error(string $message);

    /**
     * Should write log line with status DEBUG
     *
     * @param string $message some text
     */
    public function debug(string $message);
}
