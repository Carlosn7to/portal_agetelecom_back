<?php

use Illuminate\Support\Str;

return [

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

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DATABASE_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        'aws_admin' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL_AWS_ADMIN'),
            'host' => env('DB_HOST_AWS_ADMIN', '127.0.0.1'),
            'port' => env('DB_PORT_AWS_ADMIN', '3306'),
            'database' => env('DB_DATABASE_AWS_ADMIN', 'forge'),
            'username' => env('DB_USERNAME_AWS_ADMIN', 'forge'),
            'password' => env('DB_PASSWORD_AWS_ADMIN', ''),
            'unix_socket' => env('DB_SOCKET_AWS_ADMIN', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        'mysql_datawarehouse' => [
        'driver' => 'mysql',
        'url' => env('DATABASE_URL_DW'),
        'host' => env('DB_HOST_DW', '127.0.0.1'),
        'port' => env('DB_PORT_DW', '3306'),
        'database' => env('DB_DATABASE_DW', 'forge'),
        'username' => env('DB_USERNAME_DW', 'forge'),
        'password' => env('DB_PASSWORD_DW', ''),
        'unix_socket' => env('DB_SOCKET_DW', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => null,
        'options' => extension_loaded('pdo_mysql') ? array_filter([
            PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
        ]) : [],
        ],
        'mysql_integrator' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL_USER_INTEGRATOR'),
            'host' => env('DB_HOST_USER_INTEGRATOR', '127.0.0.1'),
            'port' => env('DB_PORT_USER_INTEGRATOR', '3306'),
            'database' => env('DB_DATABASE_USER_INTEGRATOR', 'forge'),
            'username' => env('DB_USERNAME_USER_INTEGRATOR', 'forge'),
            'password' => env('DB_PASSWORD_USER_INTEGRATOR', ''),
            'unix_socket' => env('DB_SOCKET_USER_INTEGRATOR', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        'mysql_take' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL_MYSQL_TAKE'),
            'host' => env('DB_HOST_MYSQL_TAKE', '127.0.0.1'),
            'port' => env('DB_PORT_MYSQL_TAKE', '3306'),
            'database' => env('DB_DATABASE_MYSQL_TAKE', 'forge'),
            'username' => env('DB_USERNAME_MYSQL_TAKE', 'forge'),
            'password' => env('DB_PASSWORD_MYSQL_TAKE', ''),
            'unix_socket' => env('DB_SOCKET_MYSQL_TAKE', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => false,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        'report' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL_REPORT'),
            'host' => env('DB_HOST_REPORT', '127.0.0.1'),
            'port' => env('DB_PORT_REPORT', '3306'),
            'database' => env('DB_DATABASE_REPORT', 'forge'),
            'username' => env('DB_USERNAME_REPORT', 'forge'),
            'password' => env('DB_PASSWORD_REPORT', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL_PGSQL'),
            'host' => env('DB_HOST_PGSQL', '127.0.0.1'),
            'port' => env('DB_PORT_PGSQL', '5432'),
            'database' => env('DB_DATABASE_PGSQL', 'forge'),
            'username' => env('DB_USERNAME_PGSQL', 'forge'),
            'password' => env('DB_PASSWORD_PGSQL', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'voalle_dw' => [
            'driver' => 'pgsql',
            'url' => env('DATABASE_URL_PGSQL'),
            'host' => env('DB_HOST_VOALLE_DW', '127.0.0.1'),
            'port' => env('DB_PORT_VOALLE_DW', '5432'),
            'database' => env('DB_DATABASE_VOALLE_DW', 'forge'),
            'username' => env('DB_USERNAME_VOALLE_DW', 'forge'),
            'password' => env('DB_PASSWORD_VOALLE_DW', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ]

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

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],

    ],

];
