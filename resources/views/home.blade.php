@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="font-weight-bold mb-5">Üdvözöljük a <span class="text-success">SemmiSzemét</span> Viszonteladó Portálján!
        </h1>

        @if(!$billingo)
            <div class="alert alert-danger rounded-lg mb-4">
                <div class="row no-gutters">
                    <div class="col-md-auto">
                        <div class="bg-danger-pastel d-flex align-items-center justify-content-center rounded-lg mr-3"
                             style="width: 48px; height: 48px;">
                                <span class="icon text-danger-pastel">
                                    <i class="fas fa-exclamation-circle"></i>
                                </span>
                        </div>
                    </div>
                    <div class="col-md">
                        <p class="mb-0">Jelenleg az automatikus számla kiállítás a fiókjához nincs helyesen
                            beállítva.</p>
                        <p class="mb-0">Kérjük vegye fel a kapcsolatot egy adminisztrátorral, hogy működjön az
                            automatikus számla kiállítás.</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="row mb-5">
            <div class="col">
                <div class="row mb-2">
                    <div class="col">
                        <h4 class="font-weight-bold mb-0">Legfrissebb hírek</h4>
                    </div>
                    @if(Auth::user()->admin)
                        <div class="col-auto">
                            <a href="{{ action('PostController@create') }}" class="btn btn-teal btn-sm">Bejegyzés hozzáadása</a>
                        </div>
                    @endif
                </div>
                <div class="card card-body">
                    @php /** @var \App\Post $post */ @endphp
                    @foreach($news as $post)
                        <div class="row {{ $news->last() != $post ? 'mb-4' : '' }}">
                            <div class="col-auto" style="width: 128px;">
                                <a href="{{ action('PostController@showPublic', $post) }}">
                                    <img src="{{ $post->getThumbnailUrl() }}" alt="" class="mw-100 d-block rounded-lg border-bottom">
                                </a>
                            </div>
                            <div class="col">
                                <a href="{{ action('PostController@showPublic', $post) }}" class="h4 font-weight-bold lh-100">{{ $post->title }}</a>
                                <p class="mb-2 lh-100">
                                    <small class="text-muted">
                                        <b class="text-dark">{{ $post->author->name }}</b>
                                        <span> - {{ $post->created_at->format('Y.m.d H:i:s') }}</span>
                                    </small>
                                </p>
                                <p class="text-muted mb-0">{{ \Illuminate\Support\Str::limit(html_entity_decode(strip_tags($post->content)), 155) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col">
                <div class="row mb-2">
                    <div class="col">
                        <h4 class="font-weight-bold mb-0">Mai teendők</h4>
                    </div>
                    <div class="col-auto">
                        <a href="{{ action('OrderTodoController@create') }}" class="btn btn-teal btn-sm">Teendő hozzáadása</a>
                    </div>
                </div>

                <div class="card card-body">
                    @if(count($todos) > 0)
                        @php /** @var \App\OrderTodo $todo */ @endphp
                        @foreach($todos as $todo)
                            <div class="row no-gutters @if($todos->last() != $todo) mb-3 @endif">
                                <div class="col-auto pr-3">
                                    @if($todo->isCompleted())
                                        <a href="{{ action('OrderTodoController@toggle', $todo) }}" class="has-tooltip text-success btn btn-sm px-1" data-toggle="tooltip" title="Teendő visszaállítása">
                                            <svg width="16px" height="16px" viewBox="0 0 16 16" class="bi bi-check-square-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm10.03 4.97a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"></path>
                                            </svg>
                                        </a>
                                    @else
                                        <a href="{{ action('OrderTodoController@toggle', $todo) }}" class="has-tooltip text-success btn btn-sm px-1" data-toggle="tooltip" title="Teendő teljesítése">
                                            <svg width="16px" height="16px" viewBox="0 0 16 16" class="bi bi-square" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M14 1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"></path>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                                <div class="col">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <p class="text-small font-weight-bold mb-0" style="line-height: 1;">{{ $todo->deadline->format('Y.m.d H:i:s') }}</p>
                                            <p class="lead mb-0">{{ $todo->content }}</p>
                                        </div>
                                        <div class="col-12 col-md-auto text-md-right">
                                            <div class="d-flex">
                                                @if($todo->order)
                                                    <a href="{{ action('OrderController@show', $todo->order->inner_resource_id) }}" class="px-0 btn btn-sm btn-link text-decoration-none mr-2">Megrendelés megtekintése</a>
                                                @endif
                                                <a href="{{ action('OrderTodoController@edit', $todo) }}" class="has-tooltip btn-muted btn btn-sm px-1" data-toggle="tooltip" title="Teendő szerkesztése">
                                                    <svg width="16px" height="16px" viewBox="0 0 16 16" class="bi bi-pen-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd" d="M13.498.795l.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001z"></path>
                                                    </svg>
                                                </a>
                                                <form action="{{ action('OrderTodoController@destroy', $todo) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="has-tooltip btn-muted btn btn-sm px-1 btn-del-comment" data-toggle="tooltip" title="Teendő törlése">
                                                        <svg width="16px" height="16px" viewBox="0 0 16 16" class="bi bi-trash2-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M2.037 3.225l1.684 10.104A2 2 0 0 0 5.694 15h4.612a2 2 0 0 0 1.973-1.671l1.684-10.104C13.627 4.224 11.085 5 8 5c-3.086 0-5.627-.776-5.963-1.775z"></path>
                                                            <path fill-rule="evenodd" d="M12.9 3c-.18-.14-.497-.307-.974-.466C10.967 2.214 9.58 2 8 2s-2.968.215-3.926.534c-.477.16-.795.327-.975.466.18.14.498.307.975.466C5.032 3.786 6.42 4 8 4s2.967-.215 3.926-.534c.477-.16.795-.327.975-.466zM8 5c3.314 0 6-.895 6-2s-2.686-2-6-2-6 .895-6 2 2.686 2 6 2z"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="lead mb-0">Nincsenek mára teendőid!</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col">
                <h4 class="font-weight-bold mb-0">Statisztika</h4>
            </div>
            <div class="col text-right">
                <p class="text-muted mb-0">Eddig a hónapban</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="card card-body">
                    <div class="row align-items-center">
                        <div class="col-md-auto">
                            <div class="bg-info-pastel d-flex align-items-center justify-content-center rounded-lg"
                                 style="width: 48px; height: 48px;">
                                <span class="icon text-info-pastel">
                                    <i class="fas fa-clipboard"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-md">
                            <p class="text-muted font-weight-bold text-uppercase mb-0">Bevétel</p>
                            <p class="h2 font-weight-bold mb-0">{{ number_format($income, 0, '.', ' ') }}
                                Ft</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-body">
                    <div class="row align-items-center">
                        <div class="col-md-auto">
                            <div class="bg-danger-pastel d-flex align-items-center justify-content-center rounded-lg"
                                 style="width: 48px; height: 48px;">
                                <span class="icon text-danger-pastel">
                                    <i class="fas fa-wallet"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-md">
                            <p class="text-muted text-uppercase font-weight-bold mb-0">Kiadások</p>
                            <p class="h2 font-weight-bold mb-0">{{ number_format($expense, 0, '.', ' ') }}
                                Ft</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-body">
                    <div class="row align-items-center">
                        <div class="col-md-auto">
                            <div class="bg-success-pastel d-flex align-items-center justify-content-center rounded-lg"
                                 style="width: 48px; height: 48px;">
                                <span class="icon text-success-pastel">
                                    <i class="fas fa-percent"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-md">
                            <p class="text-muted text-uppercase mb-0 font-weight-bold">Profit</p>
                            <p class="h2 font-weight-bold @if($profit > 0) text-success @else text-danger @endif mb-0">{{ number_format($profit, 0, '.', ' ') }}
                                Ft</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(() => {
            const $btnDelTodo = $('.btn-del-todo');
            $btnDelTodo.on('click', e => {
                if (!confirm('Biztosan törölni szeretnéd a teendőt? Ez a folyamat nem visszafordítható.')) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection
