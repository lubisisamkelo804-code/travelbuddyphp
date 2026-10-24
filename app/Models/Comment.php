<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['post_id', 'author_id', 'body', 'parent_id'];

    public function post() { return $this->belongsTo(Post::class); }
    public function author() { return $this->belongsTo(User::class, 'author_id'); }
    public function parent() { return $this->belongsTo(Comment::class, 'parent_id'); }
    public function replies() { return $this->hasMany(Comment::class, 'parent_id')->oldest(); }
    public function reactions() { return $this->hasMany(CommentReaction::class); }
}