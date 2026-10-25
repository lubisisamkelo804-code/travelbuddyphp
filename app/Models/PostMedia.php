<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostMedia extends Model
{
    protected $fillable = ['post_id', 'kind', 'path', 'position'];

    public function post() { return $this->belongsTo(Post::class); }

    public function getUrlAttribute(): string
    {
        // storage_path() works even if public/storage symlink is broken;
        // prefer the public URL when the link exists.
        if (is_link(public_path('storage')) || file_exists(public_path('storage/' . $this->path))) {
            return asset('storage/' . $this->path);
        }

        return route('media.show', $this);
    }
}