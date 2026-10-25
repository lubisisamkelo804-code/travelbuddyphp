<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'username', 'password', 'role', 'bio',
        'avatar_url', 'avatar_path', 'google_id', 'budget_level',
        'website', 'social_instagram', 'social_facebook', 'social_x',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['email_verified_at' => 'datetime', 'password' => 'hashed'];

    public const ROLE_VISITOR = 'visitor';
    public const ROLE_OWNER = 'destination_owner';
    public const ROLE_CREATOR = 'event_creator';

    public function interests() { return $this->belongsToMany(Interest::class, 'user_interests'); }
    public function destinations() { return $this->hasMany(Destination::class, 'owner_id'); }
    public function events() { return $this->hasMany(Event::class, 'creator_id'); }
    public function reviews() { return $this->hasMany(Review::class, 'author_id'); }
    public function bookings() { return $this->hasMany(Booking::class); }
    public function posts() { return $this->hasMany(Post::class, 'author_id'); }
    public function notifications() { return $this->hasMany(Notification::class); }
    public function conversations() { return $this->belongsToMany(Conversation::class)->withTimestamps(); }
    public function conversationUsers() { return $this->hasMany(ConversationUser::class, 'user_id'); }

    public function isOwner(): bool { return $this->role === self::ROLE_OWNER; }
    public function isCreator(): bool { return $this->role === self::ROLE_CREATOR; }
    public function isVisitor(): bool { return $this->role === self::ROLE_VISITOR; }

    public function avatarSource(): ?string
    {
        if ($this->avatar_path) return asset('storage/' . $this->avatar_path);
        return $this->avatar_url ?: null;
    }
}