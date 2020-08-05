@extends('layouts.app')

@php /** @var \App\Post $post */@endphp
@section('content')
    <div class="container">
        <div class="row align-items-baseline">
            <div class="col">
                <h1 class="font-weight-bold mb-0">{{ $post->title }}</h1>
                <p class="mb-4 text-muted">
                    <span>Írta: </span>
                    <span class="text-dark">{{ $post->author->name }}</span>
                    <span>- {{ $post->created_at->format('Y.m.d H:i:s') }}</span>
                </p>
            </div>
        </div>

        <div class="card card-body">
            <a href="{{ url()->previous(action('UserController@home')) }}" class="btn-muted font-weight-bold text-decoration-none">
                <span class="icon icon-sm">
                    <i class="fas fa-arrow-left"></i>
                </span>
                <span>Vissza a bejegyzésekhez</span>
            </a>
            <div class="d-flex align-items-center justify-content-center mb-4">
                <img src="{{ $post->getThumbnailUrl() }}" alt="" class="mw-100">
            </div>
            <div class="post-content">{!! $post->content !!}</div>
        </div>
    </div>
@endsection