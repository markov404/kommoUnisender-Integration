<?php

namespace Sync\Services;

use Interfaces\LoggerInterface;
use Abstractions\AbsService;

/**
 * Class SumService.
 *
 * Дочерний класс абстрактного AbsService.
 *
 * Должен складывать 2 и больше чисел, в случае НЕ верных
 * входных данных вернуть 400 код.
 *
 * @package Sync\Services\SumService
 * @author mmarkov mmarkov@team.amocrm.com
 */
class SumService extends AbsService
{
    /** @var int считает количество валидных данных */
    private int $parametersCounter;


    /**
     *
     * Constructor
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        parent::__construct($logger);
        $this->parametersCounter = 0; // Initialising 
    }

    /**
     *
     * Дергать его отсюда.
     *
     * @param array $dataList массив входных данных.
     * @return void
     */
    public function execute(array $dataList = array()): void
    {
        parent::execute($dataList);

        $sum = $this->getSum($dataList);

        if ($this->parametersCounter < 2) {
            $this->makeResponseObject(
                [null],
                400,
                "You should pass at least 2 valid values."
            );

            return;
        }

        $this->makeResponseObject([$sum], 200);

        return;
    }

    /**
     * Получение суммы чисел (если они есть)
     * из входных данных.
     *
     * @param array $dataList массив входных данных
     * @return int
     */
    private function getSum(array $dataList): int
    {
        $sum = 0;
        foreach ($dataList as $param) {
            if ($this->validateParameter($param)) {
                $sum = $sum + floatval($param);
                $this->parametersCounter++;
            }
        }

        return $sum;
    }

    /**
     * Валидация входных значений.
     *
     * @param string $param строка которая предпологается быть числом.
     * @return bool
     */
    private function validateParameter(string $param): bool
    {
        return is_numeric($param);
    }
}
