<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    protected $fillable = [
        'owner_id', 'name', 'description', 'image_url', 'city', 'country',
        'latitude', 'longitude', 'estimated_cost', 'capacity',
    ];

    protected $casts = ['estimated_cost' => 'decimal:2', 'latitude' => 'decimal:7', 'longitude' => 'decimal:7'];

    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function interests() { return $this->belongsToMany(Interest::class, 'destination_interests'); }
    public function reviews() { return $this->hasMany(Review::class)->latest(); }
    public function bookings() { return $this->hasMany(Booking::class); }
    public function events() { return $this->hasMany(Event::class); }
}