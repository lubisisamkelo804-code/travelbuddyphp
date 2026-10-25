<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InboxController extends Controller
{
    public function index()
    {
        $conversations = Auth::user()
            ->conversations()
            ->with(['users', 'lastMessage.sender'])
            ->get()
            ->sortByDesc(fn (Conversation $c) => $c->lastMessage?->created_at ?? $c->created_at)
            ->values();
        // mark all conversations as read once the user opens their inbox
        Auth::user()->conversationUsers()->update(['last_read_at' => now()]);

        $people = User::where('id', '!=', Auth::id())->orderBy('name')->limit(30)->get();

        return view('inbox.index', compact('conversations', 'people'));
    }

    public function show(Conversation $conversation)
    {
        $this->guard($conversation);
        $conversation->load(['messages.sender', 'users']);
        // mark this conversation as read for the current user
        Auth::user()->conversationUsers()
            ->where('conversation_id', $conversation->id)
            ->update(['last_read_at' => now()]);

        return view('inbox.show', compact('conversation'));
    }

    public function start(Request $r, User $user)
    {
        abort_if($user->id === Auth::id(), 403, 'You cannot message yourself.');
        $conversation = $this->findOrCreate($user->id);

        return redirect()->route('inbox.show', $conversation);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'user_id' => 'required|integer|exists:users,id|not_in:' . Auth::id(),
            'body' => 'nullable|string|max:1000',
            'share_type' => 'nullable|in:post,destination,event',
            'share_id' => 'nullable|integer',
        ]);

        $conversation = $this->findOrCreate((int) $data['user_id']);
        $this->addMessage($conversation, $data);

        return redirect()->route('inbox.show', $conversation);
    }

    public function send(Request $r, Conversation $conversation)
    {
        $this->guard($conversation);

        $data = $r->validate([
            'body' => 'nullable|string|max:1000',
            'share_type' => 'nullable|in:post,destination,event',
            'share_id' => 'nullable|integer',
        ]);

        if (empty($data['body']) && empty($data['share_type'])) {
            return back()->with('error', 'Write a message or share something.');
        }

        $this->addMessage($conversation, $data);

        return back();
    }

    private function guard(Conversation $c): void
    {
        abort_unless($c->users()->where('user_id', Auth::id())->exists(), 403);
    }

    private function findOrCreate(int $otherId): Conversation
    {
        $mine = Auth::id();

        $existing = Conversation::with('users')
            ->whereHas('users', fn ($q) => $q->where('user_id', $mine))
            ->whereHas('users', fn ($q) => $q->where('user_id', $otherId))
            ->get()
            ->first(fn ($c) => $c->users->count() === 2);

        if ($existing) return $existing;

        $conversation = Conversation::create();
        $conversation->users()->attach([$mine, $otherId]);

        return $conversation->load('users');
    }

    private function addMessage(Conversation $c, array $data): Message
    {
        $message = $c->messages()->create([
            'sender_id' => Auth::id(),
            'body' => $data['body'] ?? null,
            'share_type' => $data['share_type'] ?? null,
            'share_id' => $data['share_id'] ?? null,
        ]);

        // reset sender's read marker so the thread stays coherent
        $c->users()->updateExistingPivot(Auth::id(), ['last_read_at' => now()]);

        // alert every other participant (toast + bell notification)
        $preview = !empty($data['body'])
            ? str($data['body'])->limit(50)->toString()
            : 'Shared a ' . ($data['share_type'] ?? 'post');
        foreach ($c->users()->where('user_id', '!=', Auth::id())->get() as $recipient) {
            Notifier::send(
                $recipient,
                '💬 New message from ' . Auth::user()->name . ': ' . $preview,
                route('inbox.show', $c)
            );
        }

        return $message;
    }
}