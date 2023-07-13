<?php

namespace Utils;

use Abstractions\AbsException;
use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;


/**
 * Class Utils
 *
 * Контейнер для утилитных функций
 * @package Utils\Utils
 */
class Utils
{
    /**
     *
     * Загружает переменные среды если они не загруженны.
     * @return void
     */
    public static function loadEnvIfNotloadedAlready(): void
    {
        if (getenv("APP") === false) {
            $dotenv = Dotenv::createImmutable('./');
            $dotenv->load();
        };
    }

    /**
     *
     * Получает сегодняшнуюю дату для логирования
     *
     * @return string
     */
    public static function getCurrentDateForLogging(): string
    {
        return strval(date("Y-n-j"));
    }

    /**
     *
     * Проверяет является массив списком
     * .
     * @param array $data массив данных
     * @return bool
     */
    public static function arrayIsList(array $data): bool
    {
        if ($data === []) {
            return true;
        }
        return array_keys($data) === range(0, count($data) - 1);
    }

    /**
     * Booting eloquent
     * @param array $config
     * @return void
     */
    public static function bootEloquent(array $config = null): void
    {
        if (is_null($config)) {
            $config = $config = (include './config/autoload/database.global.php')['database']['orm'];
        }

        $capsule = new Capsule();
        $capsule->addConnection($config);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    /**
     * Check if something as input is a ModelManagerException instance.
     * @param object $something
     * @return bool
     */
    public static function isCustomException(object $something): bool
    {
        return ($something instanceof AbsException);
    }

    /**
     * Compress strings (for storing big strings in db)
     * For example if we need to store tokens and logically don`t
     * want use lot of memory because it`is not fucking text it is token.
     * 
     * By default: ZLIB Compressed Data Format Specification version 3.3 (RFC 1950)
     * 
     * @param string $str
     * @param callable $compressor
     * @return string
     */
    public static function compressString(
        string $str, callable $compressor = null): string
    {
        if (is_null($compressor)) {
            return base64_encode(gzcompress($str, 9));
        } else {
            return $compressor($str);
        }
    }

    /**
     * deCompresses strings (for storing big strings in db)
     * For example if we need to store tokens and logically don`t
     * want use lot of memory because it`is not fucking text it is token.
     * 
     * By default: ZLIB Compressed Data Format Specification version 3.3 (RFC 1950)
     * 
     * @param string $str
     * @param callable $deCompressor
     * @return string
     */
    public static function deCompressString(
        string $str, callable $deCompressor = null): string
    {
        if (is_null($deCompressor)) {
            return gzuncompress(base64_decode($str));
        } else {
            return $deCompressor($str);
        }
    }

    /**
     * If somethingA in arrayN so return valueC else return valueB
     * :) .. to reduce reapitative lines of code :)
     * 
     * @param mixed $something
     * @param array $arr
     * @param mixed $valueOne
     * @param mixed $valueTwo
     */
    public static function ifItemInArrayReturnOneElseTwo(
        $something,
        array $arr,
        $valueOne,
        $valueTwo)
    {
        if (in_array($something, $arr)) {
            return $valueOne;
        } else {
            return $valueTwo;
        };
    }
};
