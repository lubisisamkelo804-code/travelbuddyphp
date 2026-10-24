<?php

namespace App\Support;

use App\Events\UserNotified;
use App\Models\Notification;
use App\Models\User;

class Notifier
{
    public static function send(User $user, string $message, ?string $link = null): void
    {
        Notification::create(['user_id' => $user->id, 'message' => $message, 'link' => $link]);
        broadcast(new UserNotified($user->id, $message, $link))->toOthers();
    }
}