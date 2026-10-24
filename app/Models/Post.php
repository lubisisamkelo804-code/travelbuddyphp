<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = [
        'author_id', 'content', 'image_url',
        'type', 'destination_id', 'event_id', 'location_rating',
    ];

    public const TYPE_VISIT = 'visit';
    public const TYPE_BUSINESS = 'business';
    public const TYPE_EVENT = 'event';

    public function author() { return $this->belongsTo(User::class, 'author_id'); }
    public function media() { return $this->hasMany(PostMedia::class)->orderBy('position'); }
    public function destination() { return $this->belongsTo(Destination::class); }
    public function event() { return $this->belongsTo(Event::class); }
    public function comments() { return $this->hasMany(Comment::class)->whereNull('parent_id')->oldest(); }
}