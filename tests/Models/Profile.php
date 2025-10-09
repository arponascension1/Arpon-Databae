<?php

namespace Arpon\Database\Tests\Models;

use Arpon\Database\Eloquent\Model;

/**
 * Profile test model
 */
class Profile extends Model
{
   
    
    protected array $fillable = [
        'post_id',
        'bio',
        'website'
    ];

    protected array $dates = ['created_at', 'updated_at'];

    /**
     * Profile belongs to a post
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Profile belongs to a user through post
     */
    public function user()
    {
        return $this->hasOneThrough(User::class, Post::class, 'id', 'id', 'post_id', 'user_id');
    }
}