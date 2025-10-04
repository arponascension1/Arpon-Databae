<?php

namespace Arpon\Database\Tests\Models;

use Arpon\Database\Eloquent\Model;

/**
 * Comment test model
 */
class Comment extends Model
{
    
    protected array $fillable = [
        'post_id',
        'content',
        'author_name'
    ];

    protected array $dates = ['created_at', 'updated_at'];

    /**
     * Comment belongs to a post
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}