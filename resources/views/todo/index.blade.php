@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Teendők</h1>
            </div>
            <div class="col text-right">
                <a href="{{ action('OrderTodoController@create') }}" class="btn btn-teal shadow-sm">Teendő hozzáadása</a>
            </div>
        </div>

        <div class="card card-body p-md-5">
            @if($todos->count() > 0)
                @php /** @var \App\OrderTodo $todo */ @endphp
                @foreach($todos as $todo)
                    <div class="order-todo @if($todos->last() != $todo) mb-5 @endif">
                        <div class="row">
                            <div class="col-xl d-flex">
                                <div class="d-flex flex-column mr-2">
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
                                        <a href="{{ action('OrderTodoController@edit', $todo) }}" class="has-tooltip btn-muted btn btn-sm px-1" data-toggle="tooltip" title="Teendő szerkesztése">
                                            <svg width="16px" height="16px" viewBox="0 0 16 16" class="bi bi-pen-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                <path fill-rule="evenodd" d="M13.498.795l.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001z"></path>
                                            </svg>
                                        </a>
                                        <form action="{{ action('OrderTodoController@destroy', $todo) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="has-tooltip btn-muted btn btn-sm px-1 btn-del-todo" data-toggle="tooltip" title="Teendő törlése">
                                                <svg width="16px" height="16px" viewBox="0 0 16 16" class="bi bi-trash2-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M2.037 3.225l1.684 10.104A2 2 0 0 0 5.694 15h4.612a2 2 0 0 0 1.973-1.671l1.684-10.104C13.627 4.224 11.085 5 8 5c-3.086 0-5.627-.776-5.963-1.775z"></path>
                                                    <path fill-rule="evenodd" d="M12.9 3c-.18-.14-.497-.307-.974-.466C10.967 2.214 9.58 2 8 2s-2.968.215-3.926.534c-.477.16-.795.327-.975.466.18.14.498.307.975.466C5.032 3.786 6.42 4 8 4s2.967-.215 3.926-.534c.477-.16.795-.327.975-.466zM8 5c3.314 0 6-.895 6-2s-2.686-2-6-2-6 .895-6 2 2.686 2 6 2z"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                <div class="details w-100">
                                    <div class="row">
                                        <div class="col">
                                            @if($todo->isCompleted())
                                                <p class="lead font-weight-bold mb-1 text-muted" style="text-decoration: line-through;">{{ $todo->content }}</p>
                                            @else
                                                <p class="lead font-weight-bold mb-1">{{ $todo->content }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    @if(!$todo->isCompleted())
                                        <p class="text-small mb-0 row">
                                            <span class="col">Határidő:</span>
                                            <span class="col text-right font-weight-bold">{{ $todo->deadline->format('Y.m.d H:i:s') }}</span>
                                        </p>
                                    @else
                                        <p class="text-small mb-0 row">
                                            <span class="col">Teljesítve:</span>
                                            <span class="col text-right font-weight-bold">{{ $todo->completed_at->format('Y.m.d H:i:s') }}</span>
                                        </p>
                                    @endif

                                    <hr class="my-2">

                                    <p class="mb-0 row">
                                        <small class="col text-muted">Állapot hozzáadáskor: </small>
                                        <small class="col text-right" style="color: {{ $todo->status_color }};">{{ $todo->status_text }}</small>
                                    </p>
                                    <p class="mb-0 row">
                                        <small class="col d-block text-muted">Hozzáadva:</small>
                                        <small class="col text-right text-normal">{{ $todo->created_at->format('Y.m.d H:i:s') }}</small>
                                    </p>
                                </div>
                            </div>
                            <div class="col-xl">
                                @if($todo->order)
                                    <a class="d-block border rounded-lg text-decoration-none p-3 h-100" href="{{ action('OrderController@show', $todo->order->inner_resource_id) }}">
                                        <p class="font-weight-bold text-dark text-small d-flex align-items-center">
                                            <span>Kapcsolódó megrendelés</span>
                                            <span class="bs-icon ml-2">
                                                <svg width="16px" height="16px" viewBox="0 0 16 16" class="bi bi-box-arrow-up-right text-info" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                  <path fill-rule="evenodd" d="M8.636 3.5a.5.5 0 0 0-.5-.5H1.5A1.5 1.5 0 0 0 0 4.5v10A1.5 1.5 0 0 0 1.5 16h10a1.5 1.5 0 0 0 1.5-1.5V7.864a.5.5 0 0 0-1 0V14.5a.5.5 0 0 1-.5.5h-10a.5.5 0 0 1-.5-.5v-10a.5.5 0 0 1 .5-.5h6.636a.5.5 0 0 0 .5-.5z"></path>
                                                  <path fill-rule="evenodd" d="M16 .5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793L6.146 9.146a.5.5 0 1 0 .708.708L15 1.707V5.5a.5.5 0 0 0 1 0v-5z"></path>
                                                </svg>
                                            </span>
                                        </p>

                                        <p class="mb-2">
                                            <span class="bs-icon text-muted mr-2">
                                                <svg width="16px" height="16px" viewBox="0 0 16 16" class="bi bi-person-badge" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                  <path fill-rule="evenodd" d="M2 2.5A2.5 2.5 0 0 1 4.5 0h7A2.5 2.5 0 0 1 14 2.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2.5zM4.5 1A1.5 1.5 0 0 0 3 2.5v10.795a4.2 4.2 0 0 1 .776-.492C4.608 12.387 5.937 12 8 12s3.392.387 4.224.803a4.2 4.2 0 0 1 .776.492V2.5A1.5 1.5 0 0 0 11.5 1h-7z"></path>
                                                  <path fill-rule="evenodd" d="M8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM6 2.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5z"></path>
                                                </svg>
                                            </span>
                                            <span class="d-inline-flex flex-column">
                                                <span class="d-block text-dark">{{ $todo->order->firstname }} {{ $todo->order->lastname }}</span>
                                                <small class="d-block text-muted">{{ $todo->order->email }}</small>
                                            </span>
                                        </p>

                                        <p class="mb-1">
                                            <span class="bs-icon text-muted mr-2">
                                                <svg width="16px" height="16px" viewBox="0 0 16 16" class="bi bi-geo-alt" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                  <path fill-rule="evenodd" d="M12.166 8.94C12.696 7.867 13 6.862 13 6A5 5 0 0 0 3 6c0 .862.305 1.867.834 2.94.524 1.062 1.234 2.12 1.96 3.07A31.481 31.481 0 0 0 8 14.58l.208-.22a31.493 31.493 0 0 0 1.998-2.35c.726-.95 1.436-2.008 1.96-3.07zM8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10z"></path>
                                                  <path fill-rule="evenodd" d="M8 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"></path>
                                                </svg>
                                            </span>
                                            <small class="text-dark font-weight-bold">{{ $todo->order->getFormattedAddress() }}</small>
                                        </p>

                                        <p class="mb-0">
                                            <span class="bs-icon text-muted mr-2">
                                                <svg width="16px" height="16px" viewBox="0 0 16 16" class="bi bi-calendar-plus" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                  <path fill-rule="evenodd" d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"></path>
                                                  <path fill-rule="evenodd" d="M8 7a.5.5 0 0 1 .5.5V9H10a.5.5 0 0 1 0 1H8.5v1.5a.5.5 0 0 1-1 0V10H6a.5.5 0 0 1 0-1h1.5V7.5A.5.5 0 0 1 8 7z"></path>
                                                </svg>
                                            </span>
                                            <small class="text-dark">{{ $todo->order->created_at->format('Y.m.d H:i') }}</small>
                                        </p>
                                    </a>
                                @else
                                    <div class="border rounded-lg p-3 h-100">
                                        <p class="font-weight-bold mb-0">A teendőhöz nincs hozzárendelve megrendelés.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <h5 class="font-weight-normal">A megrendeléshez még nem adtál hozzá egy teendőt sem.</h5>
            @endif

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $( () => {
            const $btnDelTodo = $('.btn-del-todo');
            $btnDelTodo.on('click', e => {
                if (!confirm('Biztosan törölni szeretnéd a teendőt? Ez a folyamat nem visszafordítható.')) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection