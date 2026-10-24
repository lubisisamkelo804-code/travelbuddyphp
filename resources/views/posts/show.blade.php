@extends('layouts.app')
@section('title', 'Post')
@section('content')
<div class="post-page-head">
    <a href="{{ url()->previous() }}" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i> Back</a>
    <span class="muted-sm">Post #{{ $post->id }}</span>
</div>
@include('partials.feed-post', ['post' => $post, 'withThread' => true])
@endsection