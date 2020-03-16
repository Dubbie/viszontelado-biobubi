@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h3 class="font-weight-bold mb-4">Megrendelések</h3>
            </div>
            @if(Auth()->user()->admin)
                <div class="col text-right">
                    <a href="{{ action('ShoprenterController@updateOrders') }}" class="btn btn-sm btn-outline-secondary">Megrendelések frissítése</a>
                </div>
            @endif
        </div>

        @if(Auth()->user()->admin)
            <div class="alert alert-info">
                <p class="mb-0">Ez a fiók adminisztrátori jogkörrel rendelkezik, ezért az összes megrendelést látja.</p>
            </div>
        @endif

        <div class="card card-body">
            <table class="table table-sm table-borderless mb-0">
                <thead>
                <tr>
                    <th scope="col"><small class="font-weight-bold">Ügyfél</small></th>
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
                        <td><p class="mb-0">{{ $order->status_text }}</p></td>
                        <td><p class="mb-0">{{ $order->shipping_method_name }}</p></td>
                        <td><p class="mb-0">{{ $order->payment_method_name }}</p></td>
                        <td><p class="mb-0">{{ $order->created_at->format('Y. m. d. H:i') }}</p></td>
                        <td class="text-right"><p class="mb-0">{{ number_format($order->total, 0, '.', ' ') }} Ft</p></td>
                        <td>
                            <a href="{{ action('OrderController@show', ['orderId' => $order->inner_resource_id]) }}" class="btn-icon">
                                <span class="icon">
                                    <i class="fas fa-expand"></i>
                                </span>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endsection