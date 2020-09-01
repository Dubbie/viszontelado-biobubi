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
                            <p class="mb-0">{{ $order['order']->firstname }} {{ $order['order']->lastname }}</p>
                            <p class="mb-0">{{ $order['order']->email }}</p>
                            <p class="mb-0">{{ $order['order']->phone }}</p>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-6 col-lg-4">
                            <p class="mb-0">Szállítási cím:</p>
                        </div>
                        <div class="col-md-6 col-lg-8">
                            @if (resolve('App\Subesz\OrderService')->getFormattedAddress($order['order']) != '')
                                <p class="mb-0">{{ resolve('App\Subesz\OrderService')->getFormattedAddress($order['order']) }}</p>
                            @else
                                <p class="mb-0">Nincs megadva helyes cím</p>
                            @endif
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
                            <p class="mb-0" style="color: {{ $order['statusDescription']->color }}">{{ $order['statusDescription']->name }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-lg-3">
                    <h5 class="font-weight-bold mb-2">Termékek</h5>
                    <p class="text-muted">A megrendeléshez tartozó termékek</p>
                </div>
                <div class="col-lg-9">
                    @foreach($order['products']->items as $product)
                        <div class="row align-items-center @if(last($order['products']->items) != $product) mb-3 mb-md-1 @endif">
                            <div class="col-9 col-md-6 col-md-7 col-lg-9">
                                <p class="font-weight-bold mb-0">{{ $product->name }}</p>
                            </div>
                            <div class="col-3 col-md-2 col-lg-1 text-right text-md-center">
                                <p class="mb-0">{{ $product->stock1 }} db</p>
                            </div>
                            <div class="col-md-3 col-lg-2 text-md-right has-tooltip" data-toggle="tooltip" data-placement="left" title="Nettó egységár: {{ number_format($product->price, 0, '.', ' ') }} Ft">
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
                                <span class="h5 @if($total->type == 'TOTAL') font-weight-bold @endif">{{ number_format($total->value, 0, '.', ' ') }} Ft</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="form-group mt-4 mb-0 text-right">
                <button type="button" class="btn btn-sm btn-outline-success btn-order-status-details" data-toggle="modal"
                        data-target="#orderStatusModal" data-order-id="{{ $order['order']->id }}">Állapot módosítása
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col">
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
                                                <a href="{{ action('OrderCommentController@edit', $comment) }}" class="has-tooltip btn-muted btn btn-sm" data-toggle="tooltip" title="Megjegyzés szerkesztése">
                                                    <svg width="16px" height="16px" viewBox="0 0 16 16" class="bi bi-pen-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd" d="M13.498.795l.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001z"></path>
                                                    </svg>
                                                </a>
                                                <form action="{{ action('OrderCommentController@destroy', $comment) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="has-tooltip btn-muted btn btn-sm btn-del-comment" data-toggle="tooltip" title="Megjegyzés törlése">
                                                        <svg width="16px" height="16px" viewBox="0 0 16 16" class="bi bi-trash2-fill" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M2.037 3.225l1.684 10.104A2 2 0 0 0 5.694 15h4.612a2 2 0 0 0 1.973-1.671l1.684-10.104C13.627 4.224 11.085 5 8 5c-3.086 0-5.627-.776-5.963-1.775z"></path>
                                                            <path fill-rule="evenodd" d="M12.9 3c-.18-.14-.497-.307-.974-.466C10.967 2.214 9.58 2 8 2s-2.968.215-3.926.534c-.477.16-.795.327-.975.466.18.14.498.307.975.466C5.032 3.786 6.42 4 8 4s2.967-.215 3.926-.534c.477-.16.795-.327.975-.466zM8 5c3.314 0 6-.895 6-2s-2.686-2-6-2-6 .895-6 2 2.686 2 6 2z"></path>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="lead mb-3">{{ $comment->content }}</p>

                                    <p class="mb-0">
                                        <small>Megrendelés állapota ekkor: </small>
                                        <small style="color: {{ $comment->status_color }};">{{ $comment->status_text }}</small>
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
                            <textarea name="comment-content" id="comment-content" cols="30" rows="2" class="form-control"></textarea>
                        </div>
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-success">Megjegyzés hozzáadása</button>
                        </div>
                    </form>
                </div>
            </div>
            {{--<div class="col-lg-6">--}}
                {{--<div class="card card-body mt-4">--}}
                    {{--<h3 class="font-weight-bold mb-2">Teendők</h3>--}}
                    {{--<div id="order-comments"></div>--}}
                    {{--<form action="{{ action('OrderCommentController@store') }}" method="POST">--}}
                        {{--@csrf--}}
                        {{--<input type="hidden" name="comment-order-id" value="{{ $localOrder->id }}">--}}
                        {{--<div class="form-group">--}}
                            {{--<label for="comment-content">Megjegyzés tartalma</label>--}}
                            {{--<textarea name="comment-content" id="comment-content" cols="30" rows="10" class="form-control"></textarea>--}}
                        {{--</div>--}}
                    {{--</form>--}}
                {{--</div>--}}
            {{--</div>--}}
        </div>
    </div>

    @include('modal.order-status')
@endsection

@section('scripts')
    <script>
        $( () => {
            const modal = document.getElementById('orderStatusModal');
            const orderStatusDetails = modal.querySelector('#order-status-details');
            const loading = modal.querySelector('.modal-loader');
            const $btnDelComment = $('.btn-del-comment');

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
            $btnDelComment.on('click', e => {
                if (!confirm('Biztosan törölni szeretnéd a megjegyzést? Ez a folyamat nem visszafordítható.')) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection