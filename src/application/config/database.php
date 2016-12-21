<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       April 24, 2016
 * @brief      Database connection related actions
 * @details    
 */
use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule();
$params = [
    'driver' => getenv('DB.MYSQL.DRIVER'),
    'host' => getenv('DB.MYSQL.HOST'),
    'database' => getenv('DB.MYSQL.NAME'),
    'username' => getenv('DB.MYSQL.USERNAME'),
    'password' => getenv('DB.MYSQL.PASSWORD'),
    'charset' => getenv('DB.MYSQL.CHARSET'),
    'collation' => getenv('DB.MYSQL.COLLATION'),
    'prefix' => getenv('DB.MYSQL.PREFIX')
];
$capsule->addConnection($params);

$mongo = [
    'driver' => getenv('DB.MONGO.DRIVER'),
    'host' => getenv('DB.MONGO.HOST'),
    'port' => getenv('DB.MONGO.PORT'),
    'database' => getenv('DB.MONGO.DATABASE'),
    'username' => getenv('DB.MONGO.USERNAME'),
    'password' => getenv('DB.MONGO.PASSWORD'),
    'options' => [
        'db' => getenv('DB.MONGO.DATABASE') // sets the authentication database required by mongo 3
    ]
];

$capsule->getDatabaseManager()->extend('mongodb', function($mongo) {
    return new Jenssegers\Mongodb\Connection($mongo);
});

$capsule->addConnection($mongo,'mongodb');

$capsule->setAsGlobal();
$capsule->bootEloquent();
