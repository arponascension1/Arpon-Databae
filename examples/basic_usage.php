<?php

/**
 * Basic Usage Examples for Arpon Database
 */

require_once 'bootstrap.php';

echo "=== Arpon Database - Basic Usage Examples ===\n\n";

try {
    // 1. Create a simple table
    echo "1. Creating users table...\n";
    
    schema()->dropIfExists('users');
    schema()->create('users', function ($table) {
        $table->increments('id');
        $table->string('name', 100);
        $table->string('email')->unique();
        $table->integer('age')->nullable();
        $table->json('preferences')->nullable();
        $table->timestamps();
    });
    
    echo "   ✅ Users table created\n\n";

    // 2. Insert some data
    echo "2. Inserting sample data...\n";
    
    $userId1 = table('users')->insertGetId([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'age' => 30,
        'preferences' => json_encode(['theme' => 'dark', 'notifications' => true]),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    $userId2 = table('users')->insertGetId([
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'age' => 25,
        'preferences' => json_encode(['theme' => 'light', 'notifications' => false]),
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    echo "   ✅ Inserted users with IDs: $userId1, $userId2\n\n";

    // 3. Query data
    echo "3. Querying data...\n";
    
    $users = table('users')->get();
    echo "   📊 Found " . count($users) . " users:\n";
    
    foreach ($users as $user) {
        $name = is_array($user) ? $user['name'] : $user->name;
        $email = is_array($user) ? $user['email'] : $user->email;
        $age = is_array($user) ? $user['age'] : $user->age;
        echo "   - $name ($email) - Age: $age\n";
    }
    echo "\n";

    // 4. Update data
    echo "4. Updating data...\n";
    
    $updated = table('users')
        ->where('id', $userId1)
        ->update([
            'age' => 31,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    
    echo "   ✅ Updated $updated user(s)\n\n";

    // 5. Advanced queries
    echo "5. Advanced queries...\n";
    
    $activeUsers = table('users')
        ->where('age', '>', 25)
        ->orderBy('name')
        ->get();
    
    echo "   📊 Users older than 25: " . count($activeUsers) . "\n";
    
    $averageAge = table('users')->avg('age');
    echo "   📊 Average age: $averageAge\n\n";

    // 6. Create related table with foreign key
    echo "6. Creating posts table with foreign key...\n";
    
    schema()->dropIfExists('posts');
    schema()->create('posts', function ($table) {
        $table->increments('id');
        $table->unsignedInteger('user_id');
        $table->string('title');
        $table->text('content');
        $table->unsignedInteger('views')->default(0);
        $table->timestamps();
        
        // Foreign key with cascade delete
        $table->foreign('user_id')
              ->references('id')
              ->on('users')
              ->onDelete('cascade');
    });
    
    echo "   ✅ Posts table created with foreign key\n\n";

    // 7. Insert related data
    echo "7. Creating posts...\n";
    
    $postId1 = table('posts')->insertGetId([
        'user_id' => $userId1,
        'title' => 'My First Post',
        'content' => 'This is the content of my first post.',
        'views' => 0,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    $postId2 = table('posts')->insertGetId([
        'user_id' => $userId2,
        'title' => 'Hello World',
        'content' => 'Welcome to my blog!',
        'views' => 5,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    echo "   ✅ Created posts with IDs: $postId1, $postId2\n\n";

    // 8. Join queries
    echo "8. Join queries...\n";
    
    $postsWithAuthors = table('posts')
        ->join('users', 'users.id', '=', 'posts.user_id')
        ->select('posts.title', 'posts.views', 'users.name as author')
        ->get();
    
    echo "   📝 Posts with authors:\n";
    foreach ($postsWithAuthors as $post) {
        $title = is_array($post) ? $post['title'] : $post->title;
        $author = is_array($post) ? $post['author'] : $post->author;
        $views = is_array($post) ? $post['views'] : $post->views;
        echo "   - \"$title\" by $author ($views views)\n";
    }
    echo "\n";

    // 9. Transaction example
    echo "9. Transaction example...\n";
    
    db()->transaction(function () use ($userId1) {
        // Increment user's age
        table('users')->where('id', $userId1)->increment('age');
        
        // Add a new post
        table('posts')->insert([
            'user_id' => $userId1,
            'title' => 'Transaction Post',
            'content' => 'This post was created in a transaction.',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    });
    
    echo "   ✅ Transaction completed successfully\n\n";

    // 10. Clean up
    echo "10. Cleaning up...\n";
    schema()->dropIfExists('posts');
    schema()->dropIfExists('users');
    echo "   ✅ Tables cleaned up\n\n";

    echo "🎉 All examples completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}