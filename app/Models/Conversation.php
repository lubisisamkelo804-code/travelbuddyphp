<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [];

    public function users() { return $this->belongsToMany(User::class)->withTimestamps()->withPivot('last_read_at'); }
    public function messages() { return $this->hasMany(Message::class)->oldest(); }
    public function lastMessage() { return $this->hasOne(Message::class)->latestOfMany(); }

    public function otherUser(?User $me): ?User
    {
        return $this->users->firstWhere('id', '!=', $me?->id);
    }

    public function hasUnreadFor(User $me): bool
    {
        $last = $this->lastMessage;
        if (! $last || $last->sender_id === $me->id) return false;
        $readAt = $this->users->firstWhere('id', $me->id)?->pivot?->last_read_at;
        return $readAt === null || $readAt < $last->created_at;
    }
}