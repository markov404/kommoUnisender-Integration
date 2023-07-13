<?php

/**
 * This file is specifying settings for PHPMIG.
 * 
 * @package global
 * @author mmarkov mmarkov@team.amocrm.com
 */


use Phpmig\Adapter;
use Pimple\Container;
use Illuminate\Database\Capsule\Manager as Capsule;

$config = (include './config/autoload/database.global.php')['database']['migrations'];

$container = new Container();

$container['config'] = $config;

$container['db'] = function($c) {
    $capsule = new Capsule();
    $capsule->addConnection($c['config']);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

   return $capsule;
};

$container['phpmig.adapter'] = function($c) {
    return new Adapter\Illuminate\Database($c['db'], 'migrations');
};

$container['phpmig.migrations_path'] = '.' . DIRECTORY_SEPARATOR . 'migrations';

return $container;