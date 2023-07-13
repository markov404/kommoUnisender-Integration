<?php

namespace Interfaces;

/**
 * Interface ServiceInterface.
 *
 * Предпологается что итогом его реализации должен
 * быть паттерн команда для инкапсуляции бизнес логики в него.
 *
 * @package ServiceInterface
 */
interface ServiceInterface
{
    /**
     * Единственная отчка входа с возможной передачей входных данных.
     *
     * @param array $dataList
     * @return void;
     */
    public function execute(array $dataList = array()): void;

    /**
     * Предпологается что если что то пошло не так
     * ошибки должны ловиться и isError() должен вернуть true.
     *
     * @return bool
     */
    public function isError(): bool;

    /**
     * Use it for logger injection purposes
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger): self;

    /**
     * Метод для получения некого объекта ответа.
     *
     * @return ?ResponseInterface
     */
    public function getResponse(): ?ResponseInterface;
}
