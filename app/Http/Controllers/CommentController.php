<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Post;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $r, Post $post)
    {
        $data = $r->validate(['body' => 'required|string|min:1|max:500']);

        Comment::create([
            'post_id' => $post->id, 'author_id' => Auth::id(),
            'body' => $data['body'], 'parent_id' => null,
        ]);

        $post->increment('comment_count');

        if ($post->author_id !== Auth::id()) {
            Notifier::send($post->author, Auth::user()->name . ' commented on your post', route('posts.show', $post));
        }

        return back()->with('success', 'Comment added.');
    }

    public function reply(Request $r, Comment $comment)
    {
        $data = $r->validate(['body' => 'required|string|min:1|max:500']);

        Comment::create([
            'post_id' => $comment->post_id, 'parent_id' => $comment->id,
            'author_id' => Auth::id(), 'body' => $data['body'],
        ]);

        if ($comment->author_id !== Auth::id()) {
            Notifier::send($comment->author, Auth::user()->name . ' replied to your comment', route('posts.show', $comment->post));
        }

        return back()->with('success', 'Reply posted.');
    }

    public function react(Request $r, Comment $comment)
    {
        $data = $r->validate(['type' => 'required|in:like,dislike']);

        $existing = CommentReaction::where('comment_id', $comment->id)->where('user_id', Auth::id())->first();

        if ($existing && $existing->type === $data['type']) {
            $existing->delete();
        } else {
            CommentReaction::updateOrCreate(
                ['comment_id' => $comment->id, 'user_id' => Auth::id()],
                ['type' => $data['type']]
            );
        }

        return back();
    }

    public function destroy(Comment $comment)
    {
        abort_unless($comment->author_id === Auth::id(), 403);

        $isTop = $comment->parent_id === null;
        $postId = $comment->post_id;

        $comment->delete();

        if ($isTop) {
            $post = Post::find($postId);
            if ($post && $post->comment_count > 0) $post->decrement('comment_count');
        }

        return back()->with('success', 'Comment deleted.');
    }
}