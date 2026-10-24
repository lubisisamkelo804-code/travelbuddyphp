<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'creator_id', 'destination_id', 'title', 'description', 'image_url',
        'venue', 'city', 'latitude', 'longitude', 'start_date', 'end_date',
        'capacity', 'price', 'booking_end_date', 'external_booking_url',
    ];

    protected $casts = [
        'start_date' => 'datetime', 'end_date' => 'datetime', 'booking_end_date' => 'datetime',
        'price' => 'decimal:2', 'latitude' => 'decimal:7', 'longitude' => 'decimal:7',
    ];

    public function creator() { return $this->belongsTo(User::class, 'creator_id'); }
    public function destination() { return $this->belongsTo(Destination::class); }
    public function interests() { return $this->belongsToMany(Interest::class, 'event_interests'); }
    public function reviews() { return $this->hasMany(Review::class)->latest(); }
    public function bookings() { return $this->hasMany(Booking::class); }

    public function isPast(): bool { return $this->end_date->isPast(); }
    public function isFull(): bool { return $this->capacity !== null && $this->booking_count >= $this->capacity; }
    public function bookingClosed(): bool { return $this->booking_end_date !== null && $this->booking_end_date->isPast(); }
}