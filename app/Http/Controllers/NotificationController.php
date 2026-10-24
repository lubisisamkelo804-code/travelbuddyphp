<?php

namespace App\Http\Controllers;

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
        return response()->json(['count' => Auth::user()->notifications()->where('is_read', false)->count()]);
    }

    public function markAllRead()
    {
        Auth::user()->notifications()->update(['is_read' => true]);
        return back()->with('success', 'All notifications marked as read.');
    }
}