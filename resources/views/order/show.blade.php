@extends('layouts.app')

@section('content')
    <div class="container">
        <p class="mb-0">
            <a href="{{ action('OrderController@index') }}" class="btn btn-sm btn-link px-0 text-muted text-decoration-none">
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
                        <div class="col-7 col-md-9 col-lg-10">
                            <span class="text-muted">Nettó részösszeg:</span>
                        </div>
                        <div class="col-5 col-md-3 col-lg-2">
                            <span class="h5">{{ number_format($order['subtotal'], 0, '.', ' ') }} Ft</span>
                        </div>
                        <div class="col-7 col-md-9 col-lg-10">
                            <span class="text-muted">Áfa ({{ intval($order['order']->paymentMethodTaxRate) }}%):</span>
                        </div>
                        <div class="col-5 col-md-3 col-lg-2">
                            <span class="h5">{{ number_format(($order['order']->total - $order['subtotal']), 0, '.', ' ') }} Ft</span>
                        </div>
                        <div class="col-7 col-md-9 col-lg-10">
                            <span class="text-muted">Bruttó részösszeg:</span>
                        </div>
                        <div class="col-5 col-md-3 col-lg-2">
                            <span class="font-weight-bold h5">{{ number_format($order['order']->total, 0, '.', ' ') }} Ft</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group mt-4 mb-0 text-right">
                <button type="button" class="btn btn-sm btn-success btn-order-status-details" data-toggle="modal"
                        data-target="#orderStatusModal" data-order-id="{{ $order['order']->id }}">Állapot módosítása
                </button>
            </div>
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
        });
    </script>
@endsection