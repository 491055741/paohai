<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

if (stripos($_SERVER['SERVER_NAME'], 'quyoucard.com') !== false) {
    $dbhost = 'rdsyuqef2yuqef2.mysql.rds.aliyuncs.com';
} else {
    $dbhost = 'localhost';
}

if (stripos($_SERVER['SERVER_NAME'], 'quyou') !== false) {
    $database = 'quyou_postcard';
} else {
    $database = 'paohai_postcard';
}

return array(
    'db' => array(
        'driver'         => 'Pdo',
        'dsn'            => "mysql:dbname=$database;host=$dbhost",
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter'
                    => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
    ),
);
