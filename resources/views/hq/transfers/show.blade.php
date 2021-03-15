@php
    /** @var \App\MoneyTransfer $transfer */
@endphp
@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12 col-md">
                <p class="mb-0">
                    <a href="{{ url()->previous(action('MoneyTransferController@index')) }}"
                       class="btn-muted font-weight-bold text-decoration-none">
                        <span class="icon icon-sm">
                            <i class="fas fa-arrow-left"></i>
                        </span>
                        <span>Vissza az előző oldalra</span>
                    </a>
                </p>
                <h1 class="font-weight-bold mb-4">Átutalás részletei</h1>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <div class="card card-body">
                    {{-- Átutalás adatai --}}
                    <div class="row">
                        <div class="col-md-3">
                            <div class="d-flex">
                                <span class="icon icon-lg mr-3 text-info">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                                         class="bi bi-journal-arrow-up" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd"
                                              d="M8 11a.5.5 0 0 0 .5-.5V6.707l1.146 1.147a.5.5 0 0 0 .708-.708l-2-2a.5.5 0 0 0-.708 0l-2 2a.5.5 0 1 0 .708.708L7.5 6.707V10.5a.5.5 0 0 0 .5.5z"/>
                                        <path
                                            d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2z"/>
                                        <path
                                            d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1z"/>
                                    </svg>
                                </span>
                                <div>
                                    <p class="font-weight-bold h5 mb-1">Átutalás</p>
                                    <p class="font-weight-bold text-muted mb-4 mb-md-0" style="line-height: 1.25">Az
                                        átutalásról szóló fontos információk.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 offset-md-1">
                            <h1 class="font-weight-bolder">{{ $transfer->getId() }}</h1>
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <p class="lead font-weight-semibold text-muted mb-0">Létrehozva</p>
                                </div>
                                <div class="col-md-8">
                                    <p class="lead font-weight-semibold mb-0">{{ $transfer->created_at->format('Y.m.d H:i:s') }}</p>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-4">
                                    <p class="lead font-weight-semibold text-muted mb-0">Állapot</p>
                                </div>
                                <div class="col-md-8">
                                    <p class="{{ $transfer->getTextColorClass() }} lead font-weight-semibold mb-0">
                                        <span>{{ $transfer->getStatusText() }}</span>
                                    </p>
                                </div>
                            </div>

                            @if($transfer->isCompleted())
                                <div class="row mt-2">
                                    <div class="col-md-4">
                                        <p class="lead font-weight-semibold text-muted mb-0">Könyvelve</p>
                                    </div>
                                    <div class="col-md-8">
                                        <p class="lead font-weight-semibold mb-0">{{ $transfer->completed_at->format('Y.m.d H:i:s') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Viszonteladó --}}
                    <div class="row mt-5">
                        <div class="col-md-3">
                            <div class="d-flex">
                                <span class="icon icon-lg mr-3 text-info">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
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
                        <div class="col-md-8 offset-md-1">
                            <p class="mb-4" style="line-height: 1.25">
                                <span class="d-block font-weight-bold h3 mb-0">{{ $transfer->reseller->name }}</span>
                                <span
                                    class="d-block font-weight-bold text-muted">{{ $transfer->reseller->email }}</span>
                            </p>

                            @if($transfer->reseller->details)
                                @if($transfer->reseller->details->billing_name)
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p class="font-weight-semibold text-muted mb-0">Kedvezményezett</p>
                                        </div>
                                        <div class="col-md-8">
                                            <p class="mb-0">{{ $transfer->reseller->details->billing_name }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if($transfer->reseller->details->billing_account_number)
                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <p class="font-weight-semibold text-muted mb-0">Számlaszám</p>
                                        </div>
                                        <div class="col-md-8">
                                            <p class="mb-0">{{ $transfer->reseller->details->billing_account_number }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if($transfer->reseller->details->billing_tax_number)
                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <p class="font-weight-semibold text-muted mb-0">Adóazonosító</p>
                                        </div>
                                        <div class="col-md-8">
                                            <p class="mb-0">{{ $transfer->reseller->details->billing_tax_number }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if($transfer->reseller->details->billingAddress)
                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <p class="font-weight-semibold text-muted mb-0">Székhely</p>
                                        </div>
                                        <div class="col-md-8">
                                            <p class="mb-0">{{ $transfer->reseller->details->billingAddress->getFormattedAddress() }}</p>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <p class="font-weight-semibold mb-1">A viszonteladó számlázási adatai nincsenek
                                    kitöltve.</p>
                                <p class="mb-0">
                                    <a href="{{ action('UserController@edit', $transfer->reseller) }}"
                                       class="btn btn-sm btn-teal font-weight-semibold align-items-center d-inline-flex">
                                        <span class="icon icon-sm mr-2 text-white-50">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                 fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                                <path
                                                    d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456l-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                                <path fill-rule="evenodd"
                                                      d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/>
                                            </svg>
                                        </span>
                                        <span>Számlázási adatok kitöltése</span>
                                    </a>
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Megrendelések --}}
                    <div class="row mt-5">
                        <div class="col-md-3">
                            <div class="d-flex">
                                <span class="icon icon-lg mr-3 text-info">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor"
                                         class="bi bi-archive" viewBox="0 0 16 16">
                                        <path
                                            d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1V2zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5H2zm13-3H1v2h14V2zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5z"/>
                                    </svg>
                                </span>
                                <div>
                                    <p class="font-weight-bold h5 mb-1">Megrendelések</p>
                                    <p class="font-weight-bold text-muted mb-4 mb-md-0" style="line-height: 1.25">A
                                        kifizetésre kerülő megrendelések adatai.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 offset-md-1">
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
                            <div class="d-flex">
                                @if(!$transfer->isCompleted())
                                    <button type="button" data-toggle="modal" data-target="#completeTransfer"
                                            class="btn btn-sm btn-outline-success mr-1">Teljesítés
                                    </button>
                                @else
                                    <a href="{{ action('MoneyTransferController@downloadAttachment', $transfer) }}"
                                       class="btn btn-sm btn-primary mr-1">Csatolmány letöltése</a>
                                @endif
                                <form id="form-delete-transfer"
                                      action="{{ action('MoneyTransferController@destroy', $transfer) }}"
                                      method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-link text-muted">Átutalás törlése
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('modal.transfer.complete')
@endsection

@section('scripts')
    <script>
        $(() => {
            bsCustomFileInput.init();

            $('#form-delete-transfer').on('submit', e => {
                if (!confirm('Biztosan törölni szeretnéd az átutalásról szóló rögzítést? Ez a folyamat nem visszafordítható')) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection