<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        return view('notifications', [
            'notifications' => Auth::user()->notifications()->latest()->paginate(20),
        ]);
    }

    public function unread()
    {
        $user = Auth::user();

        return response()->json([
            'count' => $user->notifications()->where('is_read', false)->count(),
            'inbox' => $user->conversations()
                ->with('lastMessage')
                ->get()
                ->filter(fn (Conversation $c) => $c->hasUnreadFor($user))
                ->count(),
        ]);
    }

    public function markAllRead()
    {
        Auth::user()->notifications()->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }
}