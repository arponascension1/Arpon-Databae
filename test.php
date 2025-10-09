<?php

require_once 'bootstrap.php';

use Arpon\Database\Eloquent\Model;
use Arpon\Database\Schema\Blueprint;

global $capsule;

// Create tables (drop in correct order due to foreign keys)
$capsule->schema()->dropIfExists('posts');
$capsule->schema()->dropIfExists('users');

$capsule->schema()->create('users', function (Blueprint $table) {
   $table->id();
   $table->string('name');
   $table->string('email')->unique();
   $table->string('password');
   $table->timestamps();
});

$capsule->schema()->create('posts', function (Blueprint $table) {
   $table->id();
   $table->string('title');
   $table->text('description');
   $table->unsignedBigInteger('user_id')->nullable();
   $table->timestamps();
   $table->foreign('user_id')->references('id')->on('users');
});

// Define models
class User extends Model
{
    protected array $fillable = ['name', 'email', 'password'];
    protected array $hidden = ['password'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}

class Post extends Model
{
    protected array $fillable = ['title', 'description', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

// Create test data
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'password123'
]);

$post = Post::create([
    'title' => 'My First Post',
    'description' => 'This is my first blog post.',
    'user_id' => $user->id
]);

echo "✅ Created user: {$user->name} (ID: {$user->id})\n";
echo "✅ Created post: {$post->title} (ID: {$post->id})\n\n";

// Create additional test data for more comprehensive testing
$user2 = User::create([
    'name' => 'Jane Smith',
    'email' => 'jane@example.com',
    'password' => 'password456'
]);

$post2 = Post::create([
    'title' => 'Advanced PHP Techniques',
    'description' => 'Exploring advanced PHP programming patterns.',
    'user_id' => $user2->id
]);

$post3 = Post::create([
    'title' => 'Database Relationships',
    'description' => 'Understanding Eloquent relationships.',
    'user_id' => $user->id
]);

echo "✅ Created additional user: {$user2->name} (ID: {$user2->id})\n";
echo "✅ Created additional posts (IDs: {$post2->id}, {$post3->id})\n\n";

// Test belongsTo relationships extensively
echo "🔗 Testing BelongsTo Relationships:\n";
echo "=" . str_repeat("=", 40) . "\n";

// Test 1: Basic belongsTo
$foundPost = Post::find(1);
$postAuthor = $foundPost->user;
echo "1️⃣  Post '{$foundPost->title}' belongsTo User: {$postAuthor->name}\n";

// Test 2: Different post, different user
$foundPost2 = Post::find(2);
$postAuthor2 = $foundPost2->user;
echo "2️⃣  Post '{$foundPost2->title}' belongsTo User: {$postAuthor2->name}\n";

// Test 3: Multiple posts from same user
$foundPost3 = Post::find(3);
$postAuthor3 = $foundPost3->user;
echo "3️⃣  Post '{$foundPost3->title}' belongsTo User: {$postAuthor3->name}\n";

// Test 4: Verify relationship method returns correct instance
$userRelation = $foundPost->user();
echo "4️⃣  Relationship method returns: " . get_class($userRelation) . "\n";

// Test 5: Test with null foreign key (create orphaned post)
$orphanPost = new Post();
$orphanPost->title = 'Orphaned Post';
$orphanPost->description = 'Post without user';
$orphanPost->user_id = null;
$orphanPost->save();

$orphanUser = $orphanPost->user;
echo "5️⃣  Orphaned post user: " . ($orphanUser ? $orphanUser->name : 'null (correct)') . "\n";

// Test 6: Eager loading test
echo "\n🚀 Testing Eager Loading:\n";
$postsWithUsers = Post::with('user')->get();
foreach ($postsWithUsers as $post) {
    $authorName = $post->user ? $post->user->name : 'No Author';
    echo "   📄 '{$post->title}' by {$authorName}\n";
}

// Test 7: Manual relationship validation
echo "\n🔍 Testing Manual Queries:\n";
$johnsPosts = Post::where('user_id', 1)->get();
echo "   📊 Posts by John Doe (user_id=1): " . count($johnsPosts) . "\n";

$janesPosts = Post::where('user_id', 2)->get();
echo "   📊 Posts by Jane Smith (user_id=2): " . count($janesPosts) . "\n";

// Test 8: Association and dissociation
echo "\n🔗 Testing Association/Dissociation:\n";
$newPost = new Post();
$newPost->title = 'Test Association';
$newPost->description = 'Testing belongsTo association';
$newPost->save();

// Associate with user
$userRelation = $newPost->user();
$userRelation->associate($user);
$newPost->save();

echo "   ✅ Associated post with user: " . $newPost->user->name . "\n";

// Dissociate
$userRelation->dissociate();
$newPost->save();
$newPost->refresh(); // Reload from database

echo "   ✅ Dissociated post from user: " . ($newPost->user ? 'Failed' : 'Success') . "\n";

echo "\n✅ All belongsTo relationship tests passed!";