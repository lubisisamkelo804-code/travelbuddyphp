@php
    if (! $post->relationLoaded('comments')) {
        $post->load(['comments.author', 'comments.reactions', 'comments.replies.author']);
    }
@endphp
<section class="thread" id="comments">
    @forelse($post->comments as $c)
        @php $my = auth()->check() ? $c->reactions->firstWhere('user_id', auth()->id()) : null; @endphp
        <div class="cmt">
            <div class="cmt-avatar">@if($c->author->avatarSource())<img src="{{ $c->author->avatarSource() }}" alt="">@else{{ strtoupper(substr($c->author->name, 0, 1)) }}@endif</div>
            <div class="cmt-body">
                <div class="cmt-head"><b>{{ $c->author->name }}</b><span>{{ $c->created_at->diffForHumans() }}</span>
                    @auth @if(auth()->id() === $c->author_id)
                        <form method="POST" action="{{ route('comments.destroy', $c) }}" onsubmit="return confirm('Delete your comment?')">@csrf @method('DELETE')<button class="link-del">delete</button></form>
                    @endif @endauth
                </div>
                <p>{{ $c->body }}</p>
                <div class="cmt-actions">
                    @auth
                        <form method="POST" action="{{ route('comments.react', $c) }}">@csrf<input type="hidden" name="type" value="like">
                            <button class="react {{ $my && $my->type === 'like' ? 'on-like' : '' }}"><i class="fa-regular fa-thumbs-up"></i> {{ $c->reactions->where('type', 'like')->count() }}</button>
                        </form>
                        <form method="POST" action="{{ route('comments.react', $c) }}">@csrf<input type="hidden" name="type" value="dislike">
                            <button class="react {{ $my && $my->type === 'dislike' ? 'on-dis' : '' }}"><i class="fa-regular fa-thumbs-down"></i> {{ $c->reactions->where('type', 'dislike')->count() }}</button>
                        </form>
                    @endauth
                    <button type="button" class="react" data-toggle-reply="reply-{{ $c->id }}"><i class="fa-regular fa-comment-dots"></i> Reply</button>
                </div>
                @auth
                <form method="POST" action="{{ route('comments.reply', $c) }}" class="reply-form" id="reply-{{ $c->id }}" hidden>
                    @csrf
                    <input type="text" name="body" required maxlength="500" placeholder="Reply to {{ $c->author->name }}…">
                    <button class="btn-mini" type="submit">Reply</button>
                </form>
                @endauth
                @if($c->replies->count())
                <div class="replies">
                    @foreach($c->replies as $r)
                        <div class="cmt reply">
                            <div class="cmt-avatar sm">@if($r->author->avatarSource())<img src="{{ $r->author->avatarSource() }}" alt="">@else{{ strtoupper(substr($r->author->name, 0, 1)) }}@endif</div>
                            <div class="cmt-body">
                                <div class="cmt-head"><b>{{ $r->author->name }}</b><span>{{ $r->created_at->diffForHumans() }}</span>
                                    @auth @if(auth()->id() === $r->author_id)
                                        <form method="POST" action="{{ route('comments.destroy', $r) }}" onsubmit="return confirm('Delete your reply?')">@csrf @method('DELETE')<button class="link-del">delete</button></form>
                                    @endif @endauth
                                </div>
                                <p>{{ $r->body }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    @empty
        <p class="muted-sm">No comments yet — be the first to share your thoughts.</p>
    @endforelse
    @auth
        <form method="POST" action="{{ route('comments.store', $post) }}" class="cmt-form">
            @csrf
            <input type="text" name="body" required maxlength="500" placeholder="Add a comment…">
            <button class="btn-mini" type="submit"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
    @else
        <p class="muted-sm"><a href="{{ route('login') }}">Log in</a> to join the conversation.</p>
    @endauth
</section>