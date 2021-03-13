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
                        <x-steps-list-item :href="action('MoneyTransferController@chooseReseller')"
                                           :completed="true">Viszonteladó
                        </x-steps-list-item>
                        <x-steps-list-item :href="action('MoneyTransferController@chooseOrders')"
                                           :completed="true">Megrendelések
                        </x-steps-list-item>
                        <x-steps-list-item :href="action('MoneyTransferController@create')" :active="true">
                            Megerősítés
                        </x-steps-list-item>
                    </x-steps-list>

                    <h3 class="mb-4 font-weight-bold">Stimmelnek az adatok?</h3>
                    @php
                        /** @var \App\User $reseller */
                        /** @var \App\Order $order */
                    @endphp
                    <div class="row">
                        <div class="col-md-10 offset-md-1">
                            <div class="row mt-4">
                                <div class="col-6 text-left">
                                    <div class="d-flex align-items-baseline">
                                         <span class="icon icon-sm text-muted mr-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                 fill="currentColor"
                                                 class="bi bi-person-badge" viewBox="0 0 16 16">
                                              <path
                                                  d="M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1h-3zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                                              <path
                                                  d="M4.5 0A2.5 2.5 0 0 0 2 2.5V14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2.5A2.5 2.5 0 0 0 11.5 0h-7zM3 2.5A1.5 1.5 0 0 1 4.5 1h7A1.5 1.5 0 0 1 13 2.5v10.795a4.2 4.2 0 0 0-.776-.492C11.392 12.387 10.063 12 8 12s-3.392.387-4.224.803a4.2 4.2 0 0 0-.776.492V2.5z"/>
                                            </svg>
                                        </span>
                                        <p class="d-block overflow-hidden mb-2">
                                            <span
                                                class="d-block h3 font-weight-bold text-truncate mb-0 has-tooltip"
                                                data-toggle="tooltip"
                                                title="{{ $reseller->name }}">{{ $reseller->name }}</span>
                                            <span class="d-block text-muted">{{ $reseller->email }}</span>
                                        </p>
                                    </div>
                                    <p class="font-weight-bold d-flex align-items-center mb-0">
                                        <span class="icon icon-sm text-muted mr-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                 fill="currentColor"
                                                 class="bi bi-credit-card" viewBox="0 0 16 16">
                                                  <path
                                                      d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1H2zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7z"/>
                                                  <path
                                                      d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1z"/>
                                                </svg>
                                        </span>
                                        <span class="d-block font-weight-bold mr-4">00000000-00000000-00000000</span>
                                    </p>
                                    <p class="d-flex align-items-center mb-4">
                                        <span class="icon icon-sm text-muted mr-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                 fill="currentColor"
                                                 class="bi bi-cash" viewBox="0 0 16 16">
                                              <path d="M8 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/>
                                              <path
                                                  d="M0 4a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V4zm3 0a2 2 0 0 1-2 2v4a2 2 0 0 1 2 2h10a2 2 0 0 1 2-2V6a2 2 0 0 1-2-2H3z"/>
                                            </svg>
                                        </span>
                                        <span class="font-weight-bold mb-0 mr-4">{{ resolve('App\Subesz\MoneyService')->getFormattedMoney($sum) }} Ft</span>
                                    </p>
                                </div>
                                <div class="col-6">
                                    @foreach($orders as $order)
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="text-left">
                                                <span
                                                    class="d-block font-weight-bold mb-0">{{ $order->firstname }} {{ $order->lastname }}</span>
                                                <small>{{ $order->payment_method_name }}</small>
                                            </div>
                                            <span class="font-weight-bold h4">
                                                <span>{{ resolve('App\Subesz\MoneyService')->getFormattedMoney($order->total_gross) }}</span>
                                                <small class="font-weight-bold">Ft</small>
                                            </span>
                                        </div>
                                    @endforeach

                                    <hr>

                                    <p class="d-flex align-items-center justify-content-between mb-0 mt-3">
                                        <span class="text-muted mr-2">Összesen: </span>
                                        <span class="h4 font-weight-bold mb-0">
                                        <span>{{ resolve('App\Subesz\MoneyService')->getFormattedMoney($sum) }}</span>
                                        <small class="font-weight-bold">Ft</small>
                                    </span>
                                    </p>
                                </div>
                            </div>

                            <form action="{{ action('MoneyTransferController@store') }}" method="POST" class="mt-4">
                                @csrf
                                <div class="form-group d-flex justify-content-between mb-0">
                                    <a href="{{ action('MoneyTransferController@chooseOrders') }}"
                                       class="btn btn-link px-0 text-muted">Vissza</a>
                                    <button type="submit" class="btn btn-success">Rögzítem</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
