<?php

/**
 * Configuration file for database connections
 */

return [
    'default' => 'sqlite',
    
    'connections' => [
        'mysql' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', 'localhost'),
            'port'      => env('DB_PORT', 3306),
            'database'  => env('DB_DATABASE', 'test_db'),
            'username'  => env('DB_USERNAME', 'root'),
            'password'  => env('DB_PASSWORD', ''),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
        ],
        
        'sqlite' => [
            'driver'   => 'sqlite',
            'database' => __DIR__ . '/test.sqlite',
            'prefix'   => '',
        ],
    ],
];

/**
 * Simple env helper function
 */
if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }
}