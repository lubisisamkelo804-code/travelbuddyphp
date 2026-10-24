<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['author_id', 'destination_id', 'event_id', 'rating', 'body'];

    public function author() { return $this->belongsTo(User::class, 'author_id'); }
    public function destination() { return $this->belongsTo(Destination::class); }
    public function event() { return $this->belongsTo(Event::class); }
}