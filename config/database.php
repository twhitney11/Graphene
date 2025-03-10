<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PDO Fetch Style
    |--------------------------------------------------------------------------
    |
    | By default, database results will be returned as instances of the PHP
    | stdClass object; however, you may desire to retrieve records in an
    | array format for simplicity. Here you can tweak the fetch style.
    |
    */

    'fetch' => PDO::FETCH_OBJ,

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        // 'sqlite' => [
        //     'driver' => 'sqlite',
        //     'database' => env('DB_DATABASE', database_path('database.sqlite')),
        //     'prefix' => '',
        // ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', isset($_SERVER['DB_HOST'])?$_SERVER['DB_HOST']:'127.0.0.1'),
            'port' => env('DB_PORT', isset($_SERVER['DB_PORT'])?$_SERVER['DB_PORT']:'3306'),
            'database' => env('DB_DATABASE', isset($_SERVER['DB_DATABASE'])?$_SERVER['DB_DATABASE']:'graphene'),
            'username' => env('DB_USERNAME', isset($_SERVER['DB_USERNAME'])?$_SERVER['DB_USERNAME']:'graphene'),
            'password' => env('DB_PASSWORD', isset($_SERVER['DB_PASSWORD'])?$_SERVER['DB_PASSWORD']:'graphene'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
            'options' => [
                PDO::ATTR_PERSISTENT => env('DB_PERSISTENT', isset($_SERVER['DB_PERSISTENT'])?$_SERVER['DB_PERSISTENT']:false),
            ],
        ],

        // This is specifically for running a migration from legacy myBinghamton database.  Will be removed.
        // 'mysql-portal' => [
        //     'driver' => 'mysql',
        //     'host' => env('PORTAL_DB_HOST', isset($_SERVER['PORTAL_DB_HOST'])?$_SERVER['PORTAL_DB_HOST']:'127.0.0.1'),
        //     'port' => env('PORTAL_DB_PORT', isset($_SERVER['PORTAL_DB_PORT'])?$_SERVER['PORTAL_DB_PORT']:'3306'),
        //     'database' => env('PORTAL_DB_DATABASE', isset($_SERVER['PORTAL_DB_DATABASE'])?$_SERVER['PORTAL_DB_DATABASE']:'portal'),
        //     'username' => env('PORTAL_DB_USERNAME', isset($_SERVER['PORTAL_DB_USERNAME'])?$_SERVER['PORTAL_DB_USERNAME']:'graphene'),
        //     'password' => env('PORTAL_DB_PASSWORD', isset($_SERVER['PORTAL_DB_PASSWORD'])?$_SERVER['PORTAL_DB_PASSWORD']:'graphene'),
        //     'charset' => 'utf8',
        //     'collation' => 'utf8_unicode_ci',
        //     'prefix' => '',
        //     'strict' => true,
        //     'engine' => null,
        // ],

        // 'pgsql' => [
        //     'driver' => 'pgsql',
        //     'host' => env('DB_HOST', '127.0.0.1'),
        //     'port' => env('DB_PORT', '5432'),
        //     'database' => env('DB_DATABASE', 'forge'),
        //     'username' => env('DB_USERNAME', 'forge'),
        //     'password' => env('DB_PASSWORD', ''),
        //     'charset' => 'utf8',
        //     'prefix' => '',
        //     'schema' => 'public',
        //     'sslmode' => 'prefer',
        // ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    // This is specifically for running a migration from legacy myBinghamton database.  Will be removed.
    'full_seed' => env('FULL_SEED',isset($_SERVER['FULL_SEED'])?$_SERVER['FULL_SEED']:false), /* Run Portal DB seed in full/abridged mode -- faster but less complete */
    'download_images' => env('DOWNLOAD_IMAGES',isset($_SERVER['DOWNLOAD_IMAGES'])?$_SERVER['DOWNLOAD_IMAGES']:false), /* Download Images from Portal S3 Bucket */

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    // 'redis' => [

    //     'cluster' => false,

    //     'default' => [
    //         'host' => env('REDIS_HOST', '127.0.0.1'),
    //         'password' => env('REDIS_PASSWORD', null),
    //         'port' => env('REDIS_PORT', 6379),
    //         'database' => 0,
    //     ],

    // ],

];
