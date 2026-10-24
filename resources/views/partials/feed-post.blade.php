@php
    $author = $post->author;
    $media = $post->media;
    $typeLabel = $post->type === 'visit' ? 'Travel Log' : ($post->type === 'business' ? 'Official' : 'Event');
@endphp
<article class="post-card" id="post-{{ $post->id }}">
    <div class="pc-head">
        <a href="{{ route('owners.show', $author) }}" class="pc-avatar">
            @if($author->avatarSource())<img src="{{ $author->avatarSource() }}" alt="">@else{{ strtoupper(substr($author->name, 0, 1)) }}@endif
        </a>
        <div class="pc-meta">
            <a href="{{ route('owners.show', $author) }}" class="pc-name">{{ $author->name }}</a>
            <span class="pc-type type-{{ $post->type }}">{{ $typeLabel }}</span>
            <div class="pc-sub">{{ '@' . $author->username }} · {{ $post->created_at->diffForHumans() }}</div>
        </div>
        @auth @if(auth()->id() === $post->author_id)
            <form method="POST" action="{{ route('posts.destroy', $post) }}" onsubmit="return confirm('Delete this post?')">
                @csrf @method('DELETE')
                <button class="icon-btn-sm" title="Delete post"><i class="fa-regular fa-trash-can"></i></button>
            </form>
        @endif @endauth
    </div>

    <div class="pc-chips">
        @if($post->destination)
            <a class="chip chip-green" href="{{ route('destinations.show', $post->destination) }}"><i class="fa-solid fa-location-dot"></i> {{ $post->destination->name }}@if($post->location_rating)<span class="star">★ {{ number_format((float) $post->location_rating, 1) }}</span>@endif</a>
        @endif
        @if($post->event)
            <a class="chip chip-clay" href="{{ route('events.show', $post->event) }}#book"><i class="fa-regular fa-calendar"></i> {{ $post->event->title }} · Book</a>
        @endif
    </div>

    <p class="pc-text">{{ $post->content }}</p>

    @if($media->count() || $post->image_url)
    <div class="mcar" data-carousel>
        <div class="mcar-track">
            @foreach($media as $m)
                <div class="mcar-slide">
                    @if($m->kind === 'video')<video src="{{ $m->url }}" controls playsinline></video>
                    @else<img src="{{ $m->url }}" alt="" loading="lazy">@endif
                </div>
            @endforeach
            @if(!$media->count() && $post->image_url)
                <div class="mcar-slide"><img src="{{ $post->image_url }}" alt="" loading="lazy"></div>
            @endif
        </div>
        @if(($media->count() ?: ($post->image_url ? 1 : 0)) > 1)
            <button type="button" class="mcar-btn prev" data-car-prev aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
            <button type="button" class="mcar-btn next" data-car-next aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
            <div class="mcar-dots">@foreach($media as $i => $m)<span class="dot {{ $i === 0 ? 'on' : '' }}"></span>@endforeach</div>
        @endif
    </div>
    @endif

    <div class="pc-actions">
        <a class="act" href="{{ route('posts.show', $post) }}#comments"><i class="fa-regular fa-comment"></i> {{ $post->comment_count }}</a>
        <button type="button" class="act" data-toggle-share="{{ $post->id }}"><i class="fa-solid fa-share-nodes"></i> Share</button>
        @if($post->destination)<a class="act" href="{{ route('destinations.show', $post->destination) }}#reviews"><i class="fa-solid fa-star"></i> Rate</a>@endif
        <a class="act" href="{{ route('posts.show', $post) }}"><i class="fa-solid fa-arrow-up-right-from-square"></i> View post</a>
    </div>

    <div class="share-box" id="share-{{ $post->id }}" hidden>
        <button type="button" class="act" data-copy-link="{{ route('posts.show', $post) }}"><i class="fa-solid fa-link"></i> Copy link</button>
        @auth
        <form method="POST" action="{{ route('inbox.store') }}" class="share-send">
            @csrf
            <input type="hidden" name="share_type" value="post">
            <input type="hidden" name="share_id" value="{{ $post->id }}">
            <select name="user_id" required>
                <option value="">Send to a traveler…</option>
                @foreach(\App\Models\User::where('id', '!=', auth()->id())->orderBy('name')->limit(30)->get() as $u)
                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                @endforeach
            </select>
            <button class="btn-mini" type="submit">Send</button>
        </form>
        @endauth
    </div>

    @if($withThread ?? false)
        @include('partials.comment-thread', ['post' => $post])
    @endif
</article>