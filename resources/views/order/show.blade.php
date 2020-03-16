@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <h3 class="font-weight-bold mb-4">Megrendelés</h3>
            </div>
        </div>
        <div class="card card-body">
            <div class="row">
                <div class="col-md-4">
                    <p class="font-weight-bold mb-0">Rendelésazonosító:</p>
                </div>
                <div class="col-md-8">
                    <p class="mb-0">#{{ $order['order']->innerId }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <p class="font-weight-bold mb-0">Vásárló:</p>
                </div>
                <div class="col-md-8">
                    <p class="mb-0">{{ $order['order']->firstname }} {{ $order['order']->lastname }}</p>
                    <p class="mb-0">{{ $order['order']->email }}</p>
                    <p class="mb-0">{{ $order['order']->phone }}</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <p class="font-weight-bold mb-0">Szállítási cím:</p>
                </div>
                <div class="col-md-8">
                    @if (resolve('App\Subesz\OrderService')->getFormattedAddress($order['order']) != '')
                        <p class="mb-0">{{ resolve('App\Subesz\OrderService')->getFormattedAddress($order['order']) }}</p>
                    @else
                        <p class="mb-0">Nincs megadva helyes cím</p>
                    @endif
                </div>
            </div>

            <p class="mt-4 mb-4">
                <small>Sok sok adat. majd megdumáljuk mi legyen itt...</small>
            </p>

            <div class="form-group mb-0">
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