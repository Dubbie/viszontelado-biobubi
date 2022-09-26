@extends('layouts.app')

@php /** @var \App\Order $localOrder */ @endphp
@section('content')
    <div class="container">
        <p class="mb-0">
            <a href="{{ action('OrderController@index') }}" class="btn-muted font-weight-bold text-decoration-none">
                <span class="icon icon-sm">
                    <i class="fas fa-arrow-left"></i>
                </span>
                <span>Vissza a megrendelésekhez</span>
            </a>
        </p>
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Megrendelés</h1>
            </div>
        </div>

        <div class="card card-body">
            <div class="row">
                <div class="col-lg-3">
                    <h5 class="font-weight-bold mb-2">Rendelés részletek</h5>
                    <p class="text-muted">Alapvető információk a megrendelésről</p>
                </div>
                <div class="col-lg-9">
                    @if(Auth::user()->admin)
                        <div class="row mb-2">
                            <div class="col-md-6 col-lg-4">
                                <p class="text-muted mb-0">Portál Azonosító:</p>
                            </div>
                            <div class="col-md-6 col-lg-8">
                                <p class="text-muted font-weight-bold mb-0">{{ $localOrder->id }} <span
                                        class="badge badge-pill badge-success">Admin</span></p>
                            </div>
                        </div>
                    @endif
                    <div class="row mb-2">
                        <div class="col-md-6 col-lg-4">
                            <p class="mb-0">Rendelésazonosító:</p>
                        </div>
                        <div class="col-md-6 col-lg-8">
                            <p class="mb-0">#{{ $order['order']->innerId }}</p>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6 col-lg-4">
                            <p class="mb-0">Megrendelve:</p>
                        </div>
                        <div class="col-md-6 col-lg-8">
                            <p class="mb-0">{{ date('Y. m. d, H:i:s', strtotime($order['order']->dateCreated)) }}</p>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6 col-lg-4">
                            <p class="mb-0">Vásárló:</p>
                        </div>
                        <div class="col-md-6 col-lg-8">
                            <p class="font-weight-bold mb-0">{{ $order['order']->firstname }} {{ $order['order']->lastname }}</p>
                            <p class="mb-0">{{ $order['order']->email }}</p>
                            <p class="mb-0">{{ $order['order']->phone }}</p>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-6 col-lg-4">
                            <p class="mb-0">Szállítási cím:</p>
                        </div>
                        <div class="col-md-6 col-lg-8">
                            @if ($address != '')
                                <p class="mb-0">{{ $address }}</p>
                            @else
                                <p class="mb-0">Nincs megadva helyes cím</p>
                            @endif
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-6 col-lg-4">
                            <p class="mb-0">Szállítási mód:</p>
                        </div>
                        <div class="col-md-6 col-lg-8">
                            <p class="mb-0">{{ $localOrder->shipping_method_name }}</p>
                        </div>
                    </div>

                    @if(strlen($order['order']->comment) > 0)
                        <div class="row">
                            <div class="col-md-6 col-lg-4">
                                <p class="mb-0">Megjegyzés:</p>
                            </div>
                            <div class="col-md-6 col-lg-8">
                                <p class="mb-0">{{ $order['order']->comment }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6 col-lg-4">
                            <p class="mb-0">Állapot:</p>
                        </div>
                        <div class="col-md-6 col-lg-8">
                            <p class="mb-0"
                               style="color: {{ $order['statusDescription']->color }}">{{ $order['statusDescription']->name }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-lg-4">
                            <p class="mb-0">Végleges fizetés módja:</p>
                        </div>
                        <div class="col-md-6 col-lg-8">
                            <p class="mb-0 font-weight-bold">{{ $localOrder->final_payment_method }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @if(Auth::user()->admin)
                <div class="row mt-5">
                    <div class="col-lg-3">
                        <h5 class="font-weight-bold mb-2">Számla</h5>
                        <p class="text-muted">Csak <span class="badge badge-pill badge-success">Adminisztrátori</span>
                            fiók láthatja ezeket az adatokat jelenleg.</p>
                    </div>
                    <div class="col-lg-9">
                        <div class="row">
                            <div class="col-md-6 col-lg-4">
                                <p class="mb-0">Piszkozat:</p>
                            </div>
                            <div class="col-md-6 col-lg-8">
                                <p class="font-weight-bold mb-0">{{ $localOrder->draft_invoice_id ? 'Létrejött' : 'Nem jött létre' }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-lg-4">
                                <p class="mb-0">Éles számla:</p>
                            </div>
                            <div class="col-md-6 col-lg-8">
                                <p class="font-weight-bold mb-0">{{ $localOrder->invoice_id ? 'Létrejött' : 'Nem jött létre' }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 col-lg-4">
                                <p class="mb-0">Számla elmentése:</p>
                            </div>
                            <div class="col-md-6 col-lg-8">
                                <p class="font-weight-bold mb-0">{{ $localOrder->invoice_path ? 'Sikeres' : 'Sikertelen' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row mt-5">
                <div class="col-lg-3">
                    <h5 class="font-weight-bold mb-2">Termékek</h5>
                    <p class="text-muted">A megrendeléshez tartozó termékek</p>
                </div>
                <div class="col-lg-9">
                    @foreach($order['products']->items as $product)
                        <div
                            class="row align-items-center @if(last($order['products']->items) != $product) mb-3 mb-md-1 @endif">
                            <div class="col-9 col-md-6 col-md-7 col-lg-9">
                                <p class="font-weight-bold mb-0">{{ $product->name }}</p>
                            </div>
                            <div class="col-3 col-md-2 col-lg-1 text-right text-md-center">
                                <p class="mb-0">{{ $product->stock1 }} db</p>
                            </div>
                            <div class="col-md-3 col-lg-2 text-md-right has-tooltip" data-toggle="tooltip"
                                 data-placement="left"
                                 title="Nettó egységár: {{ number_format($product->price, 0, '.', ' ') }} Ft">
                                <p class="mb-0 text-muted">{{ number_format($product->total, 0, '.', ' ') }} Ft</p>
                            </div>
                        </div>
                    @endforeach
                    <div class="row no-gutters text-right mt-4">
                        @foreach($order['totals'] as $total)
                            <div class="col-7 col-md-9 col-lg-10">
                                <span class="text-muted">{{ $total->name }}</span>
                            </div>
                            <div class="col-5 col-md-3 col-lg-2">
                                <span
                                    class="h5 @if($total->type == 'TOTAL') font-weight-bold @endif">{{ number_format($total->value, 0, '.', ' ') }}
                                    Ft</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="form-group mt-4 mb-0 text-right">
                @if($localOrder->invoice_id)
                    <a href="{{ action('OrderController@downloadInvoice', ['orderId' => $localOrder->id]) }}"
                       class="btn btn-sm btn-primary">Számla letöltése</a>
                @endif
                <button type="button" class="btn btn-sm btn-outline-success btn-order-status-details"
                        data-toggle="modal"
                        data-target="#orderStatusModal" data-order-id="{{ $order['order']->id }}">Állapot módosítása
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-7">
                <div class="card card-body mt-4">
                    <h3 class="font-weight-bold mb-2">Megjegyzések</h3>
                    <div id="order-comments">
                        @if($localOrder->comments->count() > 0)
                            @php /** @var \App\OrderComment $comment */ @endphp
                            @foreach($localOrder->comments as $comment)
                                <div class="order-comment border rounded-lg p-3">
                                    <div class="row">
                                        <div class="col">
                                            <p class="text-small mb-0">
                                                <b>{{ $comment->user->name }}</b>
                                                <span class="text-muted"> - </span>
                                                <span>{{ $comment->created_at->format('Y.m.d H:i:s') }}</span>
                                            </p>
                                        </div>
                                        <div class="col-auto">
                                            <div class="d-flex">
                                                <a href="{{ action('OrderCommentController@edit', $comment) }}"
                                                   class="has-tooltip btn-muted btn btn-sm" data-toggle="tooltip"
                                                   title="Megjegyzés szerkesztése">
                                                    <svg width="16px" height="16px" viewBox="0 0 16 16"
                                                         class="bi bi-pen-fill" fill="currentColor"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd"
                                                              d="M13.498.795l.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001z"></path>
                                                    </svg>
                                                </a>
                                                <form action="{{ action('OrderCommentController@destroy', $comment) }}"
                                                      method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="has-tooltip btn-muted btn btn-sm btn-del-comment"
                                                            data-toggle="tooltip" title="Megjegyzés törlése">
                                                        <svg width="16px" height="16px" viewBox="0 0 16 16"
                                                             class="bi bi-trash2-fill" fill="currentColor"
                                                             xmlns="http://www.w3.org/2000/svg">
                                                            <path
                                                                d="M2.037 3.225l1.684 10.104A2 2 0 0 0 5.694 15h4.612a2 2 0 0 0 1.973-1.671l1.684-10.104C13.627 4.224 11.085 5 8 5c-3.086 0-5.627-.776-5.963-1.775z"></path>
                                                            <path fill-rule="evenodd"
                                                                  d="M12.9 3c-.18-.14-.497-.307-.974-.466C10.967 2.214 9.58 2 8 2s-2.968.215-3.926.534c-.477.16-.795.327-.975.466.18.14.498.307.975.466C5.032 3.786 6.42 4 8 4s2.967-.215 3.926-.534c.477-.16.795-.327.975-.466zM8 5c3.314 0 6-.895 6-2s-2.686-2-6-2-6 .895-6 2 2.686 2 6 2z"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="lead mb-3">{{ $comment->content }}</p>

                                    <p class="mb-0">
                                        <small>Megrendelés állapota ekkor: </small>
                                        <small
                                            style="color: {{ $comment->status_color }};">{{ $comment->status_text }}</small>
                                    </p>
                                </div>
                            @endforeach
                        @else
                            <h5 class="font-weight-normal">A megrendeléshez még nem fűztek hozzá megjegyzést.</h5>
                        @endif
                    </div>
                    <form action="{{ action('OrderCommentController@store') }}" method="POST" class="mt-4">
                        @csrf
                        <input type="hidden" name="comment-order-id" value="{{ $localOrder->id }}">
                        <div class="form-group">
                            <label for="comment-content">Megjegyzés tartalma</label>
                            <textarea name="comment-content" id="comment-content" cols="30" rows="6"
                                      class="form-control" required></textarea>
                        </div>
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-success">Megjegyzés hozzáadása</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card card-body mt-4">
                    <h3 class="font-weight-bold mb-2">Teendők</h3>
                    <div id="order-comments" class="list-group">
                        @if($localOrder->todos->count() > 0)
                            @php /** @var \App\OrderTodo $todo */ @endphp
                            @foreach($localOrder->todos()->orderBy('completed_at')->orderBy('deadline')->get() as $todo)
                                <div
                                    class="list-group-item order-todo d-flex p-3 @if($todo->isCompleted()) todo-complete @endif">
                                    <div class="d-flex flex-column mr-2">
                                        @if($todo->isCompleted())
                                            <a href="{{ action('OrderTodoController@toggle', $todo) }}"
                                               class="has-tooltip text-success btn btn-sm px-1" data-toggle="tooltip"
                                               title="Teendő visszaállítása">
                                                <svg width="16px" height="16px" viewBox="0 0 16 16"
                                                     class="bi bi-check-square-fill" fill="currentColor"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd"
                                                          d="M2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2zm10.03 4.97a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"></path>
                                                </svg>
                                            </a>
                                        @else
                                            <a href="{{ action('OrderTodoController@toggle', $todo) }}"
                                               class="has-tooltip text-success btn btn-sm px-1" data-toggle="tooltip"
                                               title="Teendő teljesítése">
                                                <svg width="16px" height="16px" viewBox="0 0 16 16" class="bi bi-square"
                                                     fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd"
                                                          d="M14 1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"></path>
                                                </svg>
                                            </a>
                                            <a href="{{ action('OrderTodoController@edit', $todo) }}"
                                               class="has-tooltip btn-muted btn btn-sm px-1" data-toggle="tooltip"
                                               title="Teendő szerkesztése">
                                                <svg width="16px" height="16px" viewBox="0 0 16 16"
                                                     class="bi bi-pen-fill" fill="currentColor"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd"
                                                          d="M13.498.795l.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001z"></path>
                                                </svg>
                                            </a>
                                            <form action="{{ action('OrderTodoController@destroy', $todo) }}"
                                                  method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="has-tooltip btn-muted btn btn-sm px-1 btn-del-todo"
                                                        data-toggle="tooltip" title="Teendő törlése">
                                                    <svg width="16px" height="16px" viewBox="0 0 16 16"
                                                         class="bi bi-trash2-fill" fill="currentColor"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M2.037 3.225l1.684 10.104A2 2 0 0 0 5.694 15h4.612a2 2 0 0 0 1.973-1.671l1.684-10.104C13.627 4.224 11.085 5 8 5c-3.086 0-5.627-.776-5.963-1.775z"></path>
                                                        <path fill-rule="evenodd"
                                                              d="M12.9 3c-.18-.14-.497-.307-.974-.466C10.967 2.214 9.58 2 8 2s-2.968.215-3.926.534c-.477.16-.795.327-.975.466.18.14.498.307.975.466C5.032 3.786 6.42 4 8 4s2.967-.215 3.926-.534c.477-.16.795-.327.975-.466zM8 5c3.314 0 6-.895 6-2s-2.686-2-6-2-6 .895-6 2 2.686 2 6 2z"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                    <div class="details w-100">
                                        <div class="row">
                                            <div class="col">
                                                @if($todo->isCompleted())
                                                    <p class="lead font-weight-bold mb-3 text-muted"
                                                       style="text-decoration: line-through;">{{ $todo->content }}</p>
                                                @else
                                                    <p class="lead font-weight-bold mb-3">{{ $todo->content }}</p>
                                                @endif
                                            </div>
                                        </div>

                                        @if(!$todo->isCompleted())
                                            <p class="text-small mb-0 row">
                                                <span class="col font-weight-bold">Határidő:</span>
                                                <span
                                                    class="col text-right font-weight-normal">{{ $todo->deadline->format('Y.m.d H:i:s') }}</span>
                                            </p>
                                        @else
                                            <p class="text-small mb-0 row">
                                                <span class="col font-weight-bold">Teljesítve:</span>
                                                <span
                                                    class="col text-right font-weight-normal">{{ $todo->completed_at->format('Y.m.d H:i:s') }}</span>
                                            </p>
                                        @endif

                                        <hr class="my-2">

                                        <p class="mb-0 row">
                                            <small class="col text-muted">Állapot hozzáadáskor: </small>
                                            <small class="col text-right"
                                                   style="color: {{ $todo->status_color }};">{{ $todo->status_text }}</small>
                                        </p>
                                        <p class="mb-0 row">
                                            <small class="col d-block text-muted">Hozzáadva:</small>
                                            <small
                                                class="col text-right text-normal">{{ $todo->created_at->format('Y.m.d H:i:s') }}</small>
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <h5 class="font-weight-normal">A megrendeléshez még nem adtál hozzá egy teendőt sem.</h5>
                        @endif
                    </div>
                    <form action="{{ action('OrderTodoController@store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="todo-order-id" value="{{ $localOrder->id }}">
                        <div class="form-group mt-4">
                            <label for="todo-content">Teendő</label>
                            <textarea name="todo-content" id="todo-content" cols="30" rows="1" class="form-control"
                                      required></textarea>
                        </div>

                        <div class="form-row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="todo-deadline-date">Határidő dátuma</label>
                                    <input type="date" name="todo-deadline-date" id="todo-deadline-date"
                                           class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="todo-deadline-time">Pontos időpont</label>
                                    <input type="time" name="todo-deadline-time" id="todo-deadline-time"
                                           class="form-control" value="12:00" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-sm btn-success">Teendő hozzáadása</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('modal.order-status')
@endsection

@section('scripts')
    <script>
        $(() => {
            const modal = document.getElementById('orderStatusModal');
            const orderStatusDetails = modal.querySelector('#order-status-details');
            const loading = modal.querySelector('.modal-loader');

            // Megrendelés állapot részleteinek betöltése
            $(document).on('click', '.btn-order-status-details', (e) => {
                const orderId = e.currentTarget.dataset.orderId;
                $(loading).show();
                $(orderStatusDetails).hide();
                fetch('/megrendelesek/' + orderId + '/statusz').then(response => response.text()).then(html => {
                    orderStatusDetails.innerHTML = html;
                    $(loading).hide();
                    $(orderStatusDetails).show();

                    $('.has-tooltip').tooltip();
                });
            });

            // Megj. törlés
            const $btnDelComment = $('.btn-del-comment');
            $btnDelComment.on('click', e => {
                if (!confirm('Biztosan törölni szeretnéd a megjegyzést? Ez a folyamat nem visszafordítható.')) {
                    e.preventDefault();
                }
            });

            // Teendő törlés
            const $btnDelTodo = $('.btn-del-todo');
            $btnDelTodo.on('click', e => {
                if (!confirm('Biztosan törölni szeretnéd a teendőt? Ez a folyamat nem visszafordítható.')) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection
