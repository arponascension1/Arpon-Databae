# Arpon Database - Configuration Examples

This directory contains example configuration files to help you get started with the Arpon Database library.

## Quick Start

1. **Copy example files** (these are not tracked by git):
   ```bash
   cp examples/config.example.php config.php
   cp examples/bootstrap.example.php bootstrap.php
   ```

2. **Configure your database connection** in `config.php`

3. **Run examples**:
   ```bash
   php bootstrap.php    # Initialize database
   php examples/basic_usage.php  # Basic usage example
   ```

## Example Files Available

- `config.example.php` - Database configuration template
- `bootstrap.example.php` - Bootstrap/initialization example  
- `basic_usage.php` - Basic usage examples
- `advanced_schema.php` - Advanced schema building examples
- `cascade_delete_demo.php` - Foreign key CASCADE DELETE demonstration

## Configuration

### Database Configuration (`config.php`)

```php
<?php
return [
    'default' => 'mysql',  // or 'sqlite'
    
    'connections' => [
        'mysql' => [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'your_database',
            'username'  => 'your_username', 
            'password'  => 'your_password',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
        
        'sqlite' => [
            'driver'   => 'sqlite',
            'database' => __DIR__ . '/database.sqlite',
        ],
    ],
];
```

### Bootstrap Example (`bootstrap.php`)

```php
<?php
require_once 'vendor/autoload.php';

use Arpon\Database\Capsule\Manager as DB;

$config = require 'config.php';

$capsule = new DB();

// Add connections
foreach ($config['connections'] as $name => $connection) {
    $capsule->addConnection($connection, $name);
}

// Set default connection
$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "Database initialized successfully!\n";
```

## Documentation

For complete documentation, see the [README.md](../README.md) file.