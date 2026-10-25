<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Event;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\Notifier;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with(['author', 'media', 'destination', 'event'])
            ->whereIn('type', [Post::TYPE_VISIT, Post::TYPE_BUSINESS])
            ->latest()->paginate(10);

        return view('community', compact('posts'));
    }

    public function show(Post $post)
    {
        $post->load([
            'author', 'media', 'destination', 'event',
            'comments.author', 'comments.reactions',
            'comments.replies.author', 'comments.replies.reactions',
        ]);

        return view('posts.show', compact('post'));
    }

    public function store(Request $r)
    {
        $user = $r->user();

        $type = match (true) {
            $user->isOwner() => Post::TYPE_BUSINESS,
            $user->isCreator() => Post::TYPE_EVENT,
            default => Post::TYPE_VISIT,
        };

        $data = $r->validate([
            'content' => 'required|string|min:1|max:2000',
            'destination_id' => $type === Post::TYPE_EVENT ? 'nullable|integer|exists:destinations,id' : 'required|integer|exists:destinations,id',
            'event_id' => $type === Post::TYPE_EVENT ? 'required|integer|exists:events,id' : 'nullable|integer',
            'media' => 'nullable|array|max:4',
            'media.*' => 'file|mimes:jpg,jpeg,png,webp,mp4,webm|max:20480',
        ]);

        if ($type === Post::TYPE_BUSINESS) {
            $dest = Destination::findOrFail($data['destination_id']);
            abort_unless($dest->owner_id === $user->id, 403, 'You can only post about your own business.');
        }

        if ($type === Post::TYPE_EVENT) {
            $ev = Event::findOrFail($data['event_id']);
            abort_unless($ev->creator_id === $user->id, 403, 'You can only post your own events.');
        }

        $locationRating = null;
        if (! empty($data['destination_id'])) {
            $locationRating = (float) Destination::find($data['destination_id'])->average_rating;
        }

        $post = Post::create([
            'author_id' => $user->id,
            'content' => $data['content'],
            'type' => $type,
            'destination_id' => $data['destination_id'] ?? null,
            'event_id' => $data['event_id'] ?? null,
            'location_rating' => $locationRating,
        ]);

        if ($r->hasFile('media')) {
            foreach ($r->file('media') as $i => $file) {
                $post->media()->create([
                    'kind' => str_starts_with((string) $file->getMimeType(), 'video') ? 'video' : 'image',
                    'path' => $file->store('post-media', 'public'),
                    'position' => $i,
                ]);
            }
        }

        return back()->with('success', 'Post published!');
    }

    public function like(Post $post)
    {
        $sessionKey = 'liked_post_' . $post->id;

        if ($post->author_id === Auth::id()) {
            return back()->with('error', 'You cannot like your own post.');
        }
        if (session($sessionKey)) {
            return back()->with('error', 'You have already liked this post.');
        }

        $post->increment('like_count');
        session([$sessionKey => true]);

        // alert the author (toast + bell notification)
        if ($post->author && $post->author->id !== Auth::id()) {
            Notifier::send(
                $post->author,
                '❤️ ' . Auth::user()->name . ' liked your post: ' . Str::limit($post->content, 40),
                route('posts.show', $post)
            );
        }

        return back();
    }

    public function destroy(Post $post)
    {
        abort_unless($post->author_id === Auth::id(), 403);
        $post->delete();
        return back()->with('success', 'Post deleted.');
    }
}