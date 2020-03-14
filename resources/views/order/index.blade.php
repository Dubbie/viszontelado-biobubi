@extends('layouts.app')

@section('content')
    <div class="container">
{{--        @dump($orders[0])--}}
        <div class="alert alert-info">
            <p class="mb-0">Jelenleg az összes megrendelés látható (max. 200), a szűrés implementálása jelenleg aktívan zajlik.</p>
        </div>
        <div class="card card-body">
            <table class="table table-sm table-borderless mb-0">
                <thead>
                <tr>
                    <th scope="col"><small class="font-weight-bold">Ügyfél neve/e-mail címe</small></th>
                    <th scope="col"><small class="font-weight-bold">Állapot</small></th>
                    <th scope="col"><small class="font-weight-bold">Szállítási mód</small></th>
                    <th scope="col"><small class="font-weight-bold">Fizetési mód</small></th>
                    <th scope="col"><small class="font-weight-bold">Kezdő dátum</small></th>
                    <th scope="col" class="text-right"><small class="font-weight-bold">Összesen</small></th>
                    <td></td>
                </tr>
                </thead>
                <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td><p class="mb-0">{{ $order->lastname }} {{ $order->firstname }}<small class="d-block">{{ $order->email }}</small></p></td>
                        <td><p class="mb-0">Státusz</p></td>
                        <td><p class="mb-0">{{ $order->shippingMethodName }}</p></td>
                        <td><p class="mb-0">{{ $order->paymentMethodName }}</p></td>
                        <td><p class="mb-0">{{ date('Y. m. d. H:i', strtotime($order->dateCreated)) }}</p></td>
                        <td class="text-right"><p class="mb-0">{{ number_format($order->total, 0, '.', ' ') }} Ft</p></td>
                        <td>
                            <a href="{{ action('OrderController@show', ['orderId' => $order->id]) }}" class="btn btn-sm btn-outline-secondary">Részletek</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection