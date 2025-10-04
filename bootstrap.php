<?php

/**
 * Bootstrap file for personal testing of the Database wrapper
 */

// Require the Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Require helper functions
require_once __DIR__ . '/helpers.php';

use Arpon\Database\Capsule\Manager as Capsule;

// Create a new Capsule instance
$capsule = new Capsule;

// Add SQLite connection as default (for quick testing without MySQL setup)
$capsule->addConnection([
    'driver'   => 'sqlite',
    'database' => __DIR__ . '/test.sqlite',
    'prefix'   => '',
], 'default');

// Add MySQL connection (for testing with MySQL when available)
$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'test_db',
    'username'  => 'root',
    'password'  => '',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix'    => '',
], 'mysql');

// Set the default connection
$capsule->setAsGlobal();

// Boot Eloquent
$capsule->bootEloquent();

// Make capsule globally available
global $capsule;

// Create SQLite test database and tables
$sqliteDb = __DIR__ . '/test.sqlite';

// Always recreate for testing (delete if exists)
if (file_exists($sqliteDb)) {
    unlink($sqliteDb);
}

// Create test tables using Schema Builder
try {
    // Ensure SQLite database file exists by creating it if needed
    if (!file_exists($sqliteDb)) {
        $pdo = new PDO('sqlite:' . $sqliteDb);
        $pdo->exec('CREATE TABLE IF NOT EXISTS _init (id INTEGER)');
        $pdo = null; // Close connection
    }
    
    // Get schema builder
    $schema = $capsule->schema();
    
    // Create users table with Schema Builder
    if ($schema->hasTable('users')) {
        $schema->drop('users');
    }
    
    $schema->create('users', function ($table) {
        $table->increments('id');
        $table->string('name', 100)->comment('User full name');
        $table->string('email', 150)->unique()->comment('User email address');
        $table->integer('age')->nullable()->comment('User age');
        $table->text('settings')->nullable()->comment('JSON settings');
        $table->boolean('is_active')->default(true)->comment('Account status');
        $table->timestamps(); // created_at, updated_at
    });
    
    // Create posts table with Schema Builder
    if ($schema->hasTable('posts')) {
        $schema->drop('posts');
    }
    
    $schema->create('posts', function ($table) {
        $table->increments('id');
        $table->unsignedInteger('user_id')->comment('Reference to users table');
        $table->string('title')->comment('Post title');
        $table->text('content')->nullable()->comment('Post content');
        $table->integer('views')->default(0)->comment('View count');
        $table->decimal('rating', 3, 2)->default(0.00)->comment('Average rating');
        $table->timestamps();
        
        // Foreign key constraint (works with SQLite foreign key support)
        $table->foreign('user_id')->references('id')->on('users');
    });
    
    // Create comments table with Schema Builder
    if ($schema->hasTable('comments')) {
        $schema->drop('comments');
    }
    
    $schema->create('comments', function ($table) {
        $table->increments('id');
        $table->unsignedInteger('post_id')->comment('Reference to posts table');
        $table->string('author_name', 100)->comment('Comment author name');
        $table->text('content')->comment('Comment text');
        $table->boolean('approved')->default(false)->comment('Moderation status');
        $table->timestamps();
        
        // Foreign key constraint
        $table->foreign('post_id')->references('id')->on('posts');
    });
    
    echo "SQLite database and tables created successfully with Schema Builder!\n";
    echo "Schema features demonstrated:\n";
    echo "- Blueprint-based table definition\n";
    echo "- Column types: increments, string, text, integer, decimal, boolean, timestamps\n";
    echo "- Column modifiers: nullable, default, unique, comment\n";
    echo "- Foreign key constraints\n";
    
} catch (Exception $e) {
    echo "Error creating tables: " . $e->getMessage() . "\n";
}

// Helper function to display results
function dump($data, $title = null) {
    if ($title) {
        echo "\n=== $title ===\n";
    }
    if (is_array($data) || is_object($data)) {
        print_r($data);
    } else {
        var_dump($data);
    }
    echo "\n";
}

// Helper function to run SQL and display results
function runSql($sql, $connection = 'default', $title = null) {
    try {
        global $capsule;
        $result = $capsule->connection($connection)->select($sql);
        dump($result, $title ?: "SQL: $sql");
        return $result;
    } catch (Exception $e) {
        echo "SQL Error: " . $e->getMessage() . "\n";
        return null;
    }
}

echo "Database wrapper bootstrap loaded successfully!\n";
echo "Available connections: default (sqlite), mysql\n";
echo "SQLite database location: " . $sqliteDb . "\n";
echo "\nYou can now use the database in your index.php file.\n\n";