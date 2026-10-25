<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostMedia extends Model
{
    protected $fillable = ['post_id', 'kind', 'path', 'position'];

    public function post() { return $this->belongsTo(Post::class); }

    public function getUrlAttribute(): string
    {
        // Prefer the static public URL when the storage symlink exists AND the
        // file has an extension we can serve with a correct Content-Type.
        // Otherwise fall back to the streaming route, which sets proper MIME
        // types and works even when "php artisan storage:link" was never run
        // (common on XAMPP/Windows).
        $hasLink = is_link(public_path('storage')) || is_dir(public_path('storage'));
        $hasExt  = (bool) pathinfo($this->path, PATHINFO_EXTENSION);

        if ($hasLink && $hasExt && file_exists(public_path('storage/' . $this->path))) {
            return asset('storage/' . $this->path);
        }

        return route('media.show', $this);
    }
}