@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Bejegyzések</h1>
            </div>
            @if(Auth::user()->admin)
                <div class="col text-right">
                    <a href="{{ action('PostController@create') }}" class="btn btn-teal shadow-sm">Bejegyzés hozzáadása</a>
                </div>
            @endif
        </div>
        <div class="card card-body">
            @if(count($posts) > 0)
                @php /** @var \App\Post $post */ @endphp
                @foreach($posts as $post)
                    <div class="row {{ $posts->last() != $post ? 'mb-4' : '' }}">
                        <div class="col-md">
                            <div class="row">
                                <div class="col-auto">
                                    <img src="{{ $post->getThumbnailUrl() }}" alt="" class="rounded-lg" style="width: 64px; object-fit: cover">
                                </div>
                                <div class="col">
                                    <a href="{{ action('PostController@show', $post) }}" class="h5 font-weight-bold text-decoration-none">{{ $post->title }}</a>
                                    <small class="d-block"><b>{{ $post->author->name }}</b> - {{ $post->created_at->format('Y.m.d H:i:s') }}</small>
                                    <p class="mb-0 text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($post->content), 155) }}</p>
                                </div>
                            </div>
                        </div>
                        @if(Auth::user()->admin)
                            <div class="col-md-auto">
                                <form action="{{ action('PostController@destroy', $post) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-delete-post">Törlés</button>
                                </form>
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                <p class="mb-0">Jelenleg egy bejegyzés sincs feltöltve.</p>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(() => {
            $('.btn-delete-post').on('click', e => {
                if (!confirm('Biztosan szeretnéd törölni a bejegyzést? Ezt a folyamatot nem lehet visszafordítani')) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection