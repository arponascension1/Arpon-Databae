# Arpon Database - Advanced PHP Database Abstraction Layer

[![Version](https://img.shields.io/badge/version-2.0.0-blue.svg)](https://github.com/arponascension1/Arpon-Databae)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/php-%5E7.4%7C%5E8.0-blue.svg)](https://php.net)

A powerful, Laravel-inspired database abstraction layer providing advanced schema building, query building, and ORM capabilities for MySQL and SQLite databases.

## ✨ Features

### 🏗️ Advanced Schema Builder
- **Enhanced Blueprint**: 25+ column types including JSON, UUID, enum, set, binary, longText
- **Foreign Key Constraints**: Full CASCADE DELETE/UPDATE support on both MySQL and SQLite
- **Index Management**: Create, drop, and manage indexes with composite and unique constraints
- **Table Modifications**: Add/drop columns, rename tables, and modify existing structures
- **Cross-Database Compatibility**: Consistent API across MySQL and SQLite with intelligent fallbacks

### 🔍 Powerful Query Builder
- **Fluent Interface**: Chainable methods for building complex queries
- **Advanced Joins**: Inner, left, right, and cross joins with sub-queries
- **Aggregations**: Count, sum, average, min, max with grouping
- **Raw Queries**: Execute custom SQL with parameter binding
- **Transactions**: Full ACID transaction support with rollback capabilities

### 🎯 Laravel-Compatible API
- **Familiar Syntax**: Drop-in replacement for Laravel's database components
- **Migration-Style**: Use the same Blueprint patterns you're already familiar with
- **Eloquent-Ready**: Foundation for building ORM relationships and models

## 🚀 Quick Start

### Installation

```bash
composer require arpon/database
```

### Quick Setup

1. **Copy configuration files from examples:**
   ```bash
   cp examples/config.example.php config.php
   cp examples/bootstrap.example.php bootstrap.php
   ```

2. **Update your database settings** in `config.php`

3. **Initialize the database:**
   ```php
   <?php
   require_once 'bootstrap.php';
   
   // Database is now ready to use!
   $users = table('users')->get();
   ```

### Basic Usage

```php
<?php
require_once 'vendor/autoload.php';

use Arpon\Database\Capsule\Manager as DB;

// Create database manager
$capsule = new DB();

// Add MySQL connection
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'your_database',
    'username'  => 'your_username',
    'password'  => 'your_password',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
]);

// Add SQLite connection
$capsule->addConnection([
    'driver'   => 'sqlite',
    'database' => __DIR__ . '/database.sqlite',
], 'sqlite');

// Boot the manager
$capsule->setAsGlobal();
$capsule->bootEloquent();
```

### Examples & Documentation

Complete examples are available in the `examples/` directory:
- **Configuration**: `examples/config.example.php`
- **Initialization**: `examples/bootstrap.example.php`  
- **Basic Usage**: `examples/basic_usage.php`
- **Advanced Examples**: See `examples/README.md`

## 📖 Documentation

### Schema Building

#### Creating Tables with Advanced Features

```php
$schema = $capsule->schema();

$schema->create('users', function ($table) {
    $table->increments('id');
    $table->uuid('uuid')->unique();
    $table->string('name', 100);
    $table->string('email')->unique();
    $table->json('preferences')->nullable();
    $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
    $table->softDeletes();  // Laravel-style soft deletes
    $table->timestamps();
    
    // MySQL-specific optimizations
    if ($table instanceof MySqlBlueprint) {
        $table->engine = 'InnoDB';
        $table->charset = 'utf8mb4';
    }
});

$schema->create('posts', function ($table) {
    $table->increments('id');
    $table->unsignedInteger('user_id');
    $table->string('title');
    $table->longText('content');
    $table->unsignedInteger('views')->default(0);
    $table->decimal('rating', 3, 2)->default(0.00);
    $table->timestamps();
    
    // Foreign key with CASCADE DELETE
    $table->foreign('user_id')
          ->references('id')
          ->on('users')
          ->onDelete('cascade')
          ->onUpdate('cascade');
          
    // Composite index
    $table->index(['user_id', 'created_at'], 'user_posts_index');
});
```

#### Advanced Column Types

```php
$schema->create('advanced_table', function ($table) {
    // Standard types
    $table->increments('id');
    $table->bigInteger('big_number');
    $table->decimal('price', 10, 2);
    $table->boolean('is_active');
    
    // Advanced types
    $table->json('metadata');                    // JSON column
    $table->uuid('identifier');                  // UUID column
    $table->enum('type', ['A', 'B', 'C']);     // Enum column
    $table->set('permissions', ['read', 'write', 'delete']); // Set column
    $table->binary('file_data');                // Binary data
    $table->longText('description');            // Long text
    $table->mediumText('summary');              // Medium text
    $table->tinyInteger('priority');            // Tiny integer
    
    // Laravel-style helpers
    $table->morphs('taggable');                 // Polymorphic relation columns
    $table->nullableMorphs('commentable');      // Nullable polymorphic relation
    $table->rememberToken();                    // Remember token for authentication
});
```

### Query Building

#### Basic Queries

```php
// Select queries
$users = $capsule->table('users')
    ->where('status', 'active')
    ->where('created_at', '>', '2023-01-01')
    ->orderBy('name')
    ->get();

// Insert
$userId = $capsule->table('users')->insertGetId([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'status' => 'active'
]);

// Update
$capsule->table('users')
    ->where('id', $userId)
    ->update(['status' => 'verified']);

// Delete
$capsule->table('users')
    ->where('status', 'inactive')
    ->delete();
```

#### Advanced Queries

```php
// Joins with aggregations
$results = $capsule->table('users')
    ->join('posts', 'users.id', '=', 'posts.user_id')
    ->select('users.name', 'users.email')
    ->selectRaw('COUNT(posts.id) as post_count')
    ->selectRaw('AVG(posts.rating) as avg_rating')
    ->where('users.status', 'active')
    ->groupBy('users.id')
    ->having('post_count', '>', 5)
    ->orderBy('avg_rating', 'desc')
    ->get();

// Subqueries
$popularPosts = $capsule->table('posts')
    ->whereIn('user_id', function($query) {
        $query->select('id')
              ->from('users')
              ->where('status', 'verified');
    })
    ->where('views', '>', 1000)
    ->get();
```

#### Transactions

```php
$capsule->transaction(function () use ($capsule) {
    $userId = $capsule->table('users')->insertGetId([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com'
    ]);
    
    $capsule->table('posts')->insert([
        'user_id' => $userId,
        'title' => 'My First Post',
        'content' => 'Hello World!'
    ]);
    
    // If any query fails, entire transaction rolls back
});
```

### Foreign Key CASCADE Operations

#### CASCADE DELETE Example

```php
// When a user is deleted, all their posts and comments are automatically deleted
$schema->create('users', function ($table) {
    $table->increments('id');
    $table->string('name');
    $table->timestamps();
});

$schema->create('posts', function ($table) {
    $table->increments('id');
    $table->unsignedInteger('user_id');
    $table->string('title');
    $table->timestamps();
    
    $table->foreign('user_id')
          ->references('id')
          ->on('users')
          ->onDelete('cascade');  // 🔥 Auto-delete posts when user deleted
});

$schema->create('comments', function ($table) {
    $table->increments('id');
    $table->unsignedInteger('post_id');
    $table->text('content');
    $table->timestamps();
    
    $table->foreign('post_id')
          ->references('id')
          ->on('posts')
          ->onDelete('cascade');  // 🔥 Auto-delete comments when post deleted
});

// Deleting a user automatically cascades to posts and comments
$capsule->table('users')->where('id', 1)->delete();
// ✅ User deleted, all their posts deleted, all related comments deleted
```

#### SET NULL Example

```php
$schema->create('user_profiles', function ($table) {
    $table->increments('id');
    $table->unsignedInteger('user_id')->nullable();
    $table->string('bio');
    $table->timestamps();
    
    $table->foreign('user_id')
          ->references('id')
          ->on('users')
          ->onDelete('set null');  // 🔄 Set to NULL when user deleted
});
```

## 🔧 Advanced Features

### Cross-Database Compatibility

The library automatically handles differences between MySQL and SQLite:

```php
// This works identically on both MySQL and SQLite
$schema->create('products', function ($table) {
    $table->increments('id');
    $table->json('attributes');     // JSON in MySQL, TEXT in SQLite
    $table->uuid('product_code');   // CHAR(36) in both databases
    $table->enum('status', ['new', 'used']); // Native ENUM in MySQL, TEXT with CHECK in SQLite
});
```

### Performance Optimizations

```php
// Batch insertions
$capsule->table('logs')->insert([
    ['message' => 'Log 1', 'level' => 'info'],
    ['message' => 'Log 2', 'level' => 'error'],
    ['message' => 'Log 3', 'level' => 'warning'],
]);

// Increment/Decrement
$capsule->table('posts')->increment('views');
$capsule->table('users')->decrement('credits', 5);
```

## 🧪 Testing

Run the comprehensive test suite:

```bash
# Run all tests
php mysql_index.php       # MySQL functionality test
php sqlite_cascade_test.php # SQLite CASCADE test
php cascade_test.php      # Cross-database CASCADE test

# Run PHPUnit tests (if available)
./vendor/bin/phpunit
```

## 📋 Requirements

- **PHP**: 7.4+ or 8.0+
- **MySQL**: 5.7+ or 8.0+ (optional)
- **SQLite**: 3.0+ (optional)
- **Extensions**: PDO, pdo_mysql (for MySQL), pdo_sqlite (for SQLite)

## 🔄 Version History

### Version 2.0.0 (Current)
- ✅ **Enhanced Schema Builder**: 25+ advanced column types
- ✅ **Foreign Key CASCADE**: Full ON DELETE/UPDATE support
- ✅ **Cross-Database Compatibility**: MySQL and SQLite feature parity
- ✅ **Laravel-Compatible API**: Drop-in replacement for Laravel DB components
- ✅ **Advanced Constraints**: Index management, unique constraints, composite keys
- ✅ **Performance Optimizations**: Batch operations, connection pooling

### Version 1.0.0
- Basic query builder
- Simple schema operations
- MySQL and SQLite support

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Inspired by Laravel's Eloquent ORM and Schema Builder
- Built with modern PHP practices and PSR standards
- Designed for developer productivity and database portability

---

**Made with ❤️ by [Arpon](https://github.com/arponascension1)**