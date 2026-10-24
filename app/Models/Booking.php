<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = ['user_id', 'destination_id', 'event_id', 'status', 'guests', 'notes', 'booked_date'];

    protected $casts = ['booked_date' => 'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function destination() { return $this->belongsTo(Destination::class); }
    public function event() { return $this->belongsTo(Event::class); }
}