<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlacePublished implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public array $place) {}

    public function broadcastOn(): array { return [new Channel('map')]; }

    public function broadcastWith(): array { return $this->place; }
}