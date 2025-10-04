<?php

/**
 * Index file for personal testing of the Database wrapper
 */

require_once 'bootstrap.php';

use Arpon\Database\Capsule\Manager as DB;

echo "=== Database Wrapper Personal Testing ===\n\n";

try {
    // Get the global capsule instance from bootstrap
    global $capsule;
    
    // Test 1: Basic Query Builder
    echo "1. Testing Query Builder...\n";
    
    // Insert test users (use default connection - SQLite)
    $userId1 = $capsule->table('users')->insertGetId([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    $userId2 = $capsule->table('users')->insertGetId([
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    echo "Inserted users with IDs: $userId1, $userId2\n";
    
    // Test 2: Select queries
    echo "\n2. Testing Select Queries...\n";
    $users = $capsule->table('users')->get();
    dump($users, "All Users");
    
    // Test 3: Where clauses
    echo "\n3. Testing Where Clauses...\n";
    $user = $capsule->table('users')->where('name', 'John Doe')->first();
    dump($user, "User John Doe");
    
    // Test 4: Insert posts
    echo "\n4. Testing Posts Insert...\n";
    $postId1 = $capsule->table('posts')->insertGetId([
        'user_id' => $userId1,
        'title' => 'My First Post',
        'content' => 'This is the content of my first post.',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    $postId2 = $capsule->table('posts')->insertGetId([
        'user_id' => $userId2,
        'title' => 'Another Great Post',
        'content' => 'This is another amazing post with great content.',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    echo "Inserted posts with IDs: $postId1, $postId2\n";
    
    // Test 5: Joins
    echo "\n5. Testing Joins...\n";
    $postsWithUsers = $capsule->table('posts')
        ->join('users', 'posts.user_id', '=', 'users.id')
        ->select('posts.title', 'posts.content', 'users.name as author')
        ->get();
    dump($postsWithUsers, "Posts with Authors");
    
    // Test 6: Aggregates
    echo "\n6. Testing Aggregates...\n";
    $userCount = $capsule->table('users')->count();
    $postCount = $capsule->table('posts')->count();
    echo "User count: $userCount\n";
    echo "Post count: $postCount\n";
    
    // Test 7: Update
    echo "\n7. Testing Updates...\n";
    $updated = $capsule->table('users')
        ->where('id', $userId1)
        ->update(['name' => 'John Doe Updated']);
    echo "Updated $updated user(s)\n";
    
    // Test 8: Increment/Decrement (views column already exists in our table)
    echo "\n8. Testing Increment/Decrement...\n";
    
    // Test increment
    $incremented = $capsule->table('posts')
        ->where('id', $postId1)
        ->increment('views', 5);
    echo "Incremented post views by 5 for post $postId1 (rows affected: $incremented)\n";
    
    // Check the result
    $post = $capsule->table('posts')->where('id', $postId1)->first();
    $postArray = (array) $post;
    echo "Post views now: " . ($postArray['views'] ?? 'NULL') . "\n";
    
    // Test 9: Collection methods (using Eloquent Collection)
    echo "\n9. Testing Collection methods...\n";
    
    // Test Collection toJson using Eloquent Collection
    $collection = new \Arpon\Database\Eloquent\Collection($users);
    $usersJson = $collection->toJson();
    echo "Users as JSON: " . substr($usersJson, 0, 100) . "...\n";
    
    // Test 10: Raw queries
    echo "\n10. Testing Raw Queries...\n";
    $rawResult = $capsule->connection()->select('SELECT COUNT(*) as total FROM users');
    dump($rawResult, "Raw Query Result");
    
    echo "\n=== All Tests Completed Successfully! ===\n";
    
} catch (Exception $e) {
    echo "\nError during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Optional: Clean up test data
echo "\nDo you want to clean up test data? (uncomment the lines below)\n";
/*
$capsule->table('comments')->delete();
$capsule->table('posts')->delete();
$capsule->table('users')->delete();
echo "Test data cleaned up.\n";
*/