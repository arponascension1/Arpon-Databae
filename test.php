<?php

require_once 'bootstrap.php';

use Arpon\Database\Eloquent\Model;
use Arpon\Database\Capsule\Manager as DB;

echo "=== Creating Database Tables ===\n\n";

// Drop tables in correct order (child tables first)
echo "Dropping existing tables...\n";
try {
    DB::schema()->dropIfExists('posts');  // Drop child table first
    DB::schema()->dropIfExists('users');  // Then parent table
    echo "✅ Existing tables dropped successfully!\n\n";
} catch (Exception $e) {
    echo "❌ Error dropping tables: " . $e->getMessage() . "\n\n";
}

// Create users table
echo "Creating 'users' table...\n";
try {
    DB::schema()->create('users', function ($table) {
        $table->id();  // Auto-increment primary key
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamps();  // created_at and updated_at
    });
    echo "✅ Users table created successfully!\n\n";
} catch (Exception $e) {
    echo "❌ Error creating users table: " . $e->getMessage() . "\n\n";
}

// Create posts table with foreignId()->constrained()
echo "Creating 'posts' table...\n";
try {
    DB::schema()->create('posts', function ($table) {
        $table->id();  // Auto-increment primary key
        $table->string('title');
        $table->text('description');
        $table->foreignId('user_id')->constrained()->onDelete('cascade');  // ✨ New Laravel-style foreignId!
        $table->timestamps();  // created_at and updated_at
    });
    echo "✅ Posts table created successfully!\n\n";
} catch (Exception $e) {
    echo "❌ Error creating posts table: " . $e->getMessage() . "\n\n";
}

// Define the User model
class User extends Model {
    protected array $fillable = ['name', 'email'];
    
    public function posts() {
        return $this->hasMany(Post::class);
    }
}

// Define the Post model
class Post extends Model {
    protected array $fillable = ['title', 'description', 'user_id'];
    
    public function user() {
        return $this->belongsTo(User::class);
    }
}

echo "=== Testing Table Creation ===\n\n";

// Test creating a user
echo "Creating a new user...\n";
try {
    $newUser = User::create([
        'name' => 'Jane Doe',
        'email' => 'jane.doe@example.com'
    ]);
    echo "✅ User 'Jane Doe' created with ID: {$newUser->id}\n\n";
} catch (Exception $e) {
    echo "❌ Error creating user: " . $e->getMessage() . "\n\n";
}

// --- Read ---
echo "Finding user with ID: {$newUser->id}...\n";
$foundUser = User::find($newUser->id);
if ($foundUser) {
    echo "Found user: {$foundUser->name}\n\n";
}

// --- Update ---
echo "Updating user with ID: {$newUser->id}...\n";
if ($foundUser) {
    $foundUser->name = 'Jane Smith';
    $foundUser->save();
    echo "User updated. New name: {$foundUser->name}\n\n";
}

// --- Delete ---
echo "Deleting user with ID: {$newUser->id}...\n";
if ($foundUser) {
    $foundUser->delete();
    echo "User deleted.\n\n";
}

// --- Complex Query ---
echo "Running a more complex query...\n";
// Make sure you have a user with the name 'John Doe' in your database
$johnDoes = User::where('name', 'John Doe')->orderBy('id', 'desc')->get();
if ($johnDoes->isNotEmpty()) {
    echo "Found " . $johnDoes->count() . " user(s) named 'John Doe'.\n";
    foreach ($johnDoes as $user) {
        echo "- User ID: {$user->id}\n";
    }
} else {
    echo "No users named 'John Doe' found.\n";
}

// --- Raw Query using DB facade ---
echo "\nRunning a raw query using the DB facade...\n";
try {
    $results = DB::select('SELECT * FROM users');
    echo "Found " . count($results) . " total users.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
