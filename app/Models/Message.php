<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'sender_id', 'body', 'share_type', 'share_id'];

    public function conversation() { return $this->belongsTo(Conversation::class); }
    public function sender() { return $this->belongsTo(User::class, 'sender_id'); }

    public function shareItem()
    {
        return match ($this->share_type) {
            'post' => Post::with(['author', 'media'])->find($this->share_id),
            'destination' => Destination::find($this->share_id),
            'event' => Event::find($this->share_id),
            default => null,
        };
    }
}