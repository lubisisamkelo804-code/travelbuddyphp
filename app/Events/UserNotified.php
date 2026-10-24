<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserNotified implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public int $userId, public string $message, public ?string $link = null) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->userId)];
    }

    public function broadcastWith(): array
    {
        return ['message' => $this->message, 'link' => $this->link];
    }
}