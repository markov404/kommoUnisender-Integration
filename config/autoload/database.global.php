<?php

declare(strict_types=1);
 
$databaseConnection = [
    'driver' => 'mysql',
    'username' => 'admin',
    'password' => '111111',
    'database' => 'app_db',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
];
 
return [
    'database' => [
        'migrations' => array_merge($databaseConnection, [
            'host' => '127.0.0.1',
            'port' => 3306,
        ]),
        'orm' => array_merge($databaseConnection, [
            'host' => 'application-mysql',
        ])
    ],
];