@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12 col-md">
                <h1 class="font-weight-bold mb-4">Új átutalás</h1>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <div class="card card-body text-center">
                    <x-steps-list>
                        <x-steps-list-item :href="action('MoneyTransferController@chooseReseller')" :completed="true">
                            Viszonteladó
                        </x-steps-list-item>
                        <x-steps-list-item :href="action('MoneyTransferController@chooseOrders')" :active="true">
                            Megrendelések
                        </x-steps-list-item>
                        <x-steps-list-item :href="action('MoneyTransferController@create')">Megerősítés
                        </x-steps-list-item>
                    </x-steps-list>

                    <h3 class="font-weight-bold mb-4">Melyik bankkártyás megrendeléseket szeretnéd átutalni?</h3>
                    @php /** @var \App\Order $order */ @endphp
                    <div class="row">
                        <div class="col-md-10 offset-md-1">
                            <form action="{{ action('MoneyTransferController@storeOrders') }}" method="POST"
                                  class="text-left">
                                @csrf
                                @foreach($orders as $order)
                                    <div class="custom-control custom-checkbox mb-3">
                                        <input type="checkbox" class="custom-control-input"
                                               id="ch-mt-order-{{ $order->id }}" name="mt-order-id[]"
                                               value="{{ $order->id }}">
                                        <label class="custom-control-label d-flex justify-content-between w-100"
                                               for="ch-mt-order-{{ $order->id }}">
                                    <span>
                                        <span
                                            class="d-block font-weight-bold mb-0">{{ $order->firstname }} {{ $order->lastname }}</span>
                                        <small>{{ $order->payment_method_name }}</small>
                                    </span>
                                            <span class="font-weight-bold h3">{{ resolve('App\Subesz\MoneyService')->getFormattedMoney($order->total_gross) }} Ft</span>
                                        </label>
                                    </div>
                                @endforeach

                                <div class="form-group mt-4 mb-0 d-flex justify-content-between">
                                    <a href="{{ action('MoneyTransferController@chooseReseller') }}"
                                       class="btn btn-link text-muted px-0">Vissza</a>
                                    <button type="submit" class="btn btn-success">Tovább</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
