@php
    /** @var \App\MoneyTransfer $transfer */
@endphp
@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12 col-md">
                <h1 class="font-weight-bold mb-4">Átutalás részletei</h1>
                <a href="{{ url()->previous(action('MoneyTransferController@index')) }}">Vissza</a>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <div class="card card-body">
                    {{-- Viszonteladó --}}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex">
                                <span class="icon icon-lg mr-2 text-muted">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                         class="bi bi-person-badge" viewBox="0 0 16 16">
                                      <path
                                          d="M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1h-3zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                                      <path
                                          d="M4.5 0A2.5 2.5 0 0 0 2 2.5V14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2.5A2.5 2.5 0 0 0 11.5 0h-7zM3 2.5A1.5 1.5 0 0 1 4.5 1h7A1.5 1.5 0 0 1 13 2.5v10.795a4.2 4.2 0 0 0-.776-.492C11.392 12.387 10.063 12 8 12s-3.392.387-4.224.803a4.2 4.2 0 0 0-.776.492V2.5z"/>
                                    </svg>
                                </span>
                                <div>
                                    <p class="font-weight-bold h5 mb-1">Viszonteladó</p>
                                    <p class="font-weight-bold text-muted mb-4 mb-md-0" style="line-height: 1.25">Az
                                        átutalásban
                                        résztvevő kedvezményezett
                                        adatai.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <p class="mb-2" style="line-height: 1.25">
                                <span class="d-block font-weight-bold h3 mb-0">{{ $transfer->reseller->name }}</span>
                                <span
                                    class="d-block font-weight-bold text-muted">{{ $transfer->reseller->email }}</span>
                            </p>

                            <p class="d-flex mb-0">
                                <span class="icon text-muted mr-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                         class="bi bi-credit-card" viewBox="0 0 16 16">
                                      <path
                                          d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1H2zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7z"/>
                                      <path
                                          d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1z"/>
                                    </svg>
                                </span>
                                <span class="font-weight-bold">00000000-00000000-00000000</span>
                            </p>

                            @if($transfer->reseller->details)
                                <span>{{ $transfer->reseller->details->billing_name }}</span>
                                <span>{{ $transfer->reseller->details->billing_tax_number }}</span>
                                <span>{{ $transfer->reseller->details->billingAddress->getFormattedAddress() }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Megrendelések --}}
                    <div class="row mt-5">
                        <div class="col-md-4">
                            <div class="d-flex">
                                <span class="icon icon-lg mr-2 text-muted">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                                         class="bi bi-journal" viewBox="0 0 16 16">
                                      <path
                                          d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2z"/>
                                      <path
                                          d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1z"/>
                                    </svg>
                                </span>
                                <div>
                                    <p class="font-weight-bold h5 mb-1">Megrendelések</p>
                                    <p class="font-weight-bold text-muted mb-4 mb-md-0" style="line-height: 1.25">A
                                        kifizetésre kerülő megrendelések adatai.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            @php /** @var \App\Order $order */ @endphp
                            @foreach($transfer->transferOrders as $to)
                                <div class="row align-items-center mb-4">
                                    <div class="col-12 col-md-7">
                                        <p class="mb-0">#{{ $to->order->inner_id }}</p>
                                        <p class="mb-0"><b>{{ $to->order->firstname }} {{ $to->order->lastname }}</b>
                                        </p>
                                        <p class="mb-0">{{ $to->order->getFormattedAddress() }}</p>
                                    </div>
                                    <div class="col-12 col-md-5 text-md-right">
                                        <p class="h3 font-weight-semibold mb-0">@money($to->order->total_gross)<small
                                                class="font-weight-bold ml-1">Ft</small></p>
                                    </div>
                                </div>
                            @endforeach
                            <hr>
                            <div class="row align-items-baseline">
                                <div class="col-12 col-md-7">
                                    <p class="text-muted mb-0">Összesen:</p>
                                </div>
                                <div class="col-12 col-md-5 text-md-right">
                                    <p class="h3 font-weight-semibold mb-0">@money($transfer->amount)<small
                                            class="font-weight-bold ml-1">Ft</small></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Gombok --}}
                    <div class="row mt-5">
                        <div class="col-md-8 offset-md-4">
                            <button type="button" data-toggle="modal" data-target="#completeTransfer"
                                    class="btn btn-sm btn-success">Elutaltam
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('modal.transfer.complete')
@endsection
