<?php

require_once 'bootstrap.php';

//use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Capsule\Manager as DB;
use Arpon\Database\Eloquent\Model;
use Arpon\Database\Capsule\Manager as DB;
// Define the User model
class User extends Model {}

// --- Create ---
echo "Creating a new user...\n";
$newUser = new User();
$newUser->name = 'Jane Doe';
$newUser->email = 'jane.doe3@example.com';
$newUser->save();
echo "User 'Jane Doe' created with ID: {$newUser->id}\n\n";

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
