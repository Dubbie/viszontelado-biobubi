@extends('layouts.app')

@php /** @var \App\OrderComment $comment */ @endphp
@section('content')
    <div class="container">
        <p class="mb-0">
            <a href="{{ action('OrderController@show', $comment->order->inner_resource_id) }}" class="btn-muted font-weight-bold text-decoration-none">
                <span class="icon icon-sm">
                    <i class="fas fa-arrow-left"></i>
                </span>
                <span>Vissza a megrendeléshez</span>
            </a>
        </p>
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Megjegyzés szerkesztése</h1>
            </div>
        </div>

        <div class="card card-body mt-4">
            <h3 class="font-weight-bold mb-2">Megjegyzés</h3>
            <form action="{{ action('OrderCommentController@update') }}" method="POST">
                @csrf
                <input type="hidden" name="comment-id" value="{{ $comment->id }}">

                <p class="text-small mb-0">
                    <b>{{ $comment->user->name }}</b>
                    <span class="text-muted"> - </span>
                    <span>{{ $comment->created_at->format('Y.m.d H:i:s') }}</span>
                </p>

                <div class="form-group mt-3">
                    <label for="comment-content" style="display:none;">Megjegyzés tartalma</label>
                    <textarea name="comment-content" id="comment-content" cols="30" rows="2" class="form-control" required>{{ $comment->content }}</textarea>
                </div>

                <p class="mb-0">
                    <small>Megrendelés állapota ekkor: </small>
                    <small style="color: {{ $comment->status_color }};">{{ $comment->status_text }}</small>
                </p>

                <div class="form-group mb-0 mt-4">
                    <button type="submit" class="btn btn-success">Megjegyzés frissítése</button>
                </div>
            </form>
        </div>
    </div>
@endsection