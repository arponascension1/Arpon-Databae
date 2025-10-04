<?php

/**
 * Database Configuration Template
 * 
 * Copy this file to config.php and update with your database settings
 */

return [
    'default' => 'mysql', // Default connection name
    
    'connections' => [
        'mysql' => [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', 'localhost'),
            'port'      => env('DB_PORT', 3306),
            'database'  => env('DB_DATABASE', 'your_database'),
            'username'  => env('DB_USERNAME', 'your_username'),
            'password'  => env('DB_PASSWORD', 'your_password'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
        ],
        
        'sqlite' => [
            'driver'   => 'sqlite',
            'database' => env('DB_SQLITE_PATH', __DIR__ . '/database.sqlite'),
            'prefix'   => '',
        ],
    ],
];

/**
 * Helper function for environment variables
 */
if (!function_exists('env')) {
    function env($key, $default = null) {
        $value = getenv($key);
        return $value !== false ? $value : $default;
    }
}