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
            'media.*' => 'nullable,file,mimes:jpg,jpeg,png,webp,mp4,webm|max:20480',
        ]);

        // Drop empty file inputs (browsers submit an empty entry when the picker is untouched).
        $files = array_values(array_filter(
            $r->file('media') ?? [],
            fn ($f) => $f instanceof \Illuminate\Http\UploadedFile && $f->isValid()
        ));

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
            $dest = Destination::withAvg('reviews', 'rating')->find($data['destination_id']);
            $locationRating = $dest ? (float) $dest->reviews_avg_rating : null;
        }

        $post = Post::create([
            'author_id' => $user->id,
            'content' => $data['content'],
            'type' => $type,
            'destination_id' => $data['destination_id'] ?? null,
            'event_id' => $data['event_id'] ?? null,
            'location_rating' => $locationRating,
        ]);

        $saved = 0;
        foreach ($files as $i => $file) {
            try {
                // Store with the original extension so the browser serves the
                // right Content-Type (a .mp4 saved without an extension will
                // not render in <video>/<img> tags).
                $ext    = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION)) ?: $file->extension() ?: 'bin';
                $stored = $file->storeAs('post-media', Str::random(20) . '.' . $ext, 'public');

                $isVideo = str_starts_with((string) $file->getMimeType(), 'video')
                    || in_array($ext, ['mp4', 'webm', 'mov', 'm4v'], true);

                $post->media()->create([
                    'kind'     => $isVideo ? 'video' : 'image',
                    'path'     => $stored,
                    'position' => $i,
                ]);
                $saved++;
            } catch (\Throwable $e) {
                report($e);
                // Continue storing remaining files even if one fails.
            }
        }

        // If nothing was saved, flag it so the UI can tell the user why media is missing.
        if ($files && $saved === 0) {
            $post->delete();
            return back()->withInput()->withErrors(['media' => 'All uploads failed to save. Ensure PHP\'s upload_max_filesize/post_max_size allow 20 MB and that "php artisan storage:link" has been run.']);
        }

        return back()->with('success', $saved ? 'Post published with ' . $saved . ' file(s)!' : 'Post published!');
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

        // Remove uploaded files from disk before deleting the records.
        foreach ($post->media as $m) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($m->path);
        }
        $post->media()->delete();
        $post->delete();

        return back()->with('success', 'Post deleted.');
    }

    /**
     * Fallback route that streams post media directly from storage when the
     * public/storage symlink is missing (e.g. XAMPP setups where
     * "php artisan storage:link" was never run).
     */
    public function media(\App\Models\PostMedia $media)
    {
        abort_unless($media->post && $media->post->author_id !== null, 404);

        $full = storage_path('app/public/' . $media->path);
        abort_unless(is_file($full), 404);

        // Guess the correct MIME type from the file extension so browsers
        // actually render <video>/<img> tags instead of refusing to play.
        $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'mov' => 'video/quicktime',
            'm4v' => 'video/x-m4v',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };

        return response()->file($full, ['Content-Type' => $mime]);
    }
}