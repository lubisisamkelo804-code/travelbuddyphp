<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostMedia extends Model
{
    protected $fillable = ['post_id', 'kind', 'path', 'position'];

    public function post() { return $this->belongsTo(Post::class); }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }
}