<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [];

    public function users() { return $this->belongsToMany(User::class)->withTimestamps(); }
    public function messages() { return $this->hasMany(Message::class)->oldest(); }
    public function lastMessage() { return $this->hasOne(Message::class)->latestOfMany(); }

    public function otherUser(?User $me): ?User
    {
        return $this->users->firstWhere('id', '!=', $me?->id);
    }
}