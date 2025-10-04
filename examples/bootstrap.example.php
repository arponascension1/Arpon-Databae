<?php

/**
 * Bootstrap Example for Arpon Database
 * 
 * Copy this file to bootstrap.php and customize as needed
 */

require_once 'vendor/autoload.php';

use Arpon\Database\Capsule\Manager as DB;

// Load configuration
$config = require 'config.php';

// Create database manager
$capsule = new DB();

// Add connections from config
foreach ($config['connections'] as $name => $connection) {
    $capsule->addConnection($connection, $name);
}

// Set default connection
if (isset($config['default'])) {
    $capsule->addConnection($config['connections'][$config['default']], 'default');
}

// Boot Eloquent
$capsule->setAsGlobal();
$capsule->bootEloquent();

// Optional: Create schema builder for easy access
$schema = $capsule->schema();

echo "✅ Arpon Database initialized successfully!\n";
echo "Available connections: " . implode(', ', array_keys($config['connections'])) . "\n";
echo "Default connection: " . ($config['default'] ?? 'none') . "\n\n";

// Export for global use
global $capsule, $schema;

// Helper functions
if (!function_exists('db')) {
    function db($connection = null) {
        global $capsule;
        return $connection ? $capsule->connection($connection) : $capsule;
    }
}

if (!function_exists('schema')) {
    function schema($connection = null) {
        global $capsule;
        return $connection ? $capsule->connection($connection)->schema() : $capsule->schema();
    }
}

if (!function_exists('table')) {
    function table($table, $connection = null) {
        global $capsule;
        return $connection ? $capsule->connection($connection)->table($table) : $capsule->table($table);
    }
}