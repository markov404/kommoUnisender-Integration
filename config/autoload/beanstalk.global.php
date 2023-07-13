<?php

/**
 * Config global file with Beanstalk config.
 * 
 * @package global
 * @author mmarkov mmarkov@team.amocrm.com
 */

return [
    'beanstalk' => [
        'host' => 'application-beanstalkd',
        'port' => 11300,
        'timeout' => 10,
    ]
];