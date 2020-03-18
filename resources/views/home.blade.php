@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="font-weight-bold mb-5">Üdvözöljük a <span class="text-success">BioBubi</span> Viszonteladó Portálján!</h1>

        <h4 class="font-weight-bold mb-2">Legutolsó megrendelés</h4>
        <div class="card card-body mb-4">
            <table class="table table-responsive-lg table-sm table-borderless mb-0">
                <thead>
                    <tr>
                        <th scope="col">
                            <small>Ügyfél</small>
                        </th>
                        <th scope="col">
                            <small>Állapot</small>
                        </th>
                        <th scope="col">
                            <small>Város</small>
                        </th>
                        <th scope="col">
                            <small>Kezdő dátum</small>
                        </th>
                        <th scope="col" class="text-right">
                            <small>Összesen</small>
                        </th>
                        <td></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <p class="mb-0">{{ $order->firstname }} {{ $order->lastname }}
                                <small class="d-block text-muted">{{ $order->email }}</small>
                            </p>
                        </td>
                        <td class="align-middle">
                            <p class="mb-0" style="color: {{ $order->status_color }}">{{ $order->status_text }}</p>
                        </td>
                        <td class="align-middle"><p class="mb-0">{{ $order->getFormattedAddress() }}</p></td>
                        <td class="align-middle"><p
                                    class="mb-0 text-nowrap">{{ $order->created_at->format('Y. m. d. H:i') }}</p>
                        </td>
                        <td class="text-right align-middle">
                            <p class="mb-0 text-nowrap">{{ number_format($order->total_gross, 0, '.', ' ') }} Ft</p>
                        </td>
                        <td class="align-middle text-right">
                            <a href="{{ action('OrderController@show', ['orderId' => $order->inner_resource_id]) }}"
                               class="btn btn-sm btn-outline-secondary">Részletek</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h4 class="font-weight-bold mb-2">Statisztika az utóbbi 7 napról</h4>
        <div class="row">
            <div class="col-md-4">
                <div class="card card-body">
                    <h5 class="text-muted font-weight-bold">Bevétel</h5>
                    <p class="h2 text-success mb-0">{{ number_format($income, 0, '.', ' ') }} Ft</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-body">
                    <h5 class="text-muted font-weight-bold">Kiadások</h5>
                    <p class="h2 mb-0">{{ number_format($expense, 0, '.', ' ') }} Ft</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-body">
                    <h5 class="text-muted font-weight-bold">Profit</h5>
                    <p class="h2 font-weight-bold @if($profit > 0) text-success @else text-danger @endif mb-0">{{ number_format($profit, 0, '.', ' ') }} Ft</p>
                </div>
            </div>
        </div>
    </div>
@endsection
