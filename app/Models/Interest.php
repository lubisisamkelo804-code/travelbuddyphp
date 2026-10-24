<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interest extends Model
{
    protected $fillable = ['name', 'icon'];

    public function users() { return $this->belongsToMany(User::class, 'user_interests'); }
    public function destinations() { return $this->belongsToMany(Destination::class, 'destination_interests'); }
    public function events() { return $this->belongsToMany(Event::class, 'event_interests'); }
}