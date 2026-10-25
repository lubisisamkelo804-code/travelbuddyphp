<div class="composer-overlay" id="composer-overlay" data-close-composer></div>
<div class="composer-sheet" id="composer-sheet" role="dialog" aria-modal="true" aria-label="Create post">
    <div class="composer-box">
        <div class="composer-head">
            <h3>Create Post</h3>
            <button type="button" class="icon-btn-sm" data-close-composer aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
            @csrf
            <textarea name="content" rows="3" required maxlength="2000" placeholder="Where are you exploring today, {{ auth()->user()->name }}?">{{ old('content') }}</textarea>
            <div class="composer-row">
                <label class="file-btn"><i class="fa-regular fa-image"></i> Add up to 4 photos/videos
                    <input type="file" name="media[]" id="composer-media" multiple accept="image/*,video/*" hidden>
                </label>
                <span class="media-count" id="media-count"></span>
            </div>
            <script>
            // Show the selected file names/count so users get feedback that
            // their picks will actually be uploaded with the post.
            (function () {
                var input = document.getElementById('composer-media');
                var out = document.getElementById('media-count');
                if (!input || !out) return;
                input.addEventListener('change', function () {
                    var n = input.files ? input.files.length : 0;
                    out.textContent = n ? (n + ' file' + (n > 1 ? 's' : '') + ' selected') : '';
                });
            })();
            </script>
            <div class="composer-row">
                @if(auth()->user()->isCreator())
                    <select name="event_id" required>
                        <option value="">Your event (required)…</option>
                        @foreach(auth()->user()->events()->orderBy('start_date')->get() as $e)
                            <option value="{{ $e->id }}">{{ $e->title }}</option>
                        @endforeach
                    </select>
                @elseif(auth()->user()->isOwner())
                    <select name="destination_id" required>
                        <option value="">Your destination (required)…</option>
                        @foreach(auth()->user()->destinations()->orderBy('name')->get() as $d)
                            <option value="{{ $d->id }}">{{ $d->name }} · {{ $d->city }}</option>
                        @endforeach
                    </select>
                @else
                    <select name="destination_id" required>
                        <option value="">Where were you? (required)…</option>
                        @foreach(\App\Models\Destination::orderBy('name')->limit(60)->get() as $d)
                            <option value="{{ $d->id }}">{{ $d->name }} · {{ $d->city }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
            @if($errors->any())<p class="form-err">{{ $errors->first() }}</p>@endif
            <div class="composer-foot">
                <span class="muted-sm">Posts appear in Home, Community & Explore per your role.</span>
                <button class="btn btn-primary btn-sm" type="submit"><i class="fa-solid fa-paper-plane"></i> Post</button>
            </div>
        </form>
    </div>
</div>