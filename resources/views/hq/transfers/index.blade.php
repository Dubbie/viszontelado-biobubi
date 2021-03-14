@extends('layouts.app')

@section('content')
    <div id="transfers-page" class="container">
        <div class="row">
            <div class="col-12 col-md">
                <h1 class="font-weight-bold mb-4">Átutalások</h1>
            </div>
            <div class="col-12 col-md-auto text-md-right">
                <a href="{{ action('MoneyTransferController@chooseReseller') }}" class="btn btn-teal">Átutalás rögzítése</a>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <div class="card card-body">
                    @if(count($transfers) > 0)
                        <div class="row align-items-center d-none d-md-flex">
                            <div class="col-12 col-md-2">
                                <p class="mb-0"><small>Azonosító</small></p>
                            </div>
                            <div class="col-12 col-md-3">
                                <p class="mb-0"><small>Viszonteladó</small></p>
                            </div>
                            <div class="col-12 col-md-2 text-md-right">
                                <p class="mb-0"><small>Összeg</small></p>
                            </div>
                            <div class="col-12 col-md-2 text-md-center">
                                <p class="mb-0"><small>Létrehozva</small></p>
                            </div>
                            <div class="col-12 col-md-2 text-md-center">
                                <p class="mb-0"><small>Állapot</small></p>
                            </div>
                        </div>
                        @php /** @var \App\MoneyTransfer $mt */ @endphp
                        @foreach($transfers as $mt)
                            <div class="row align-items-center">
                                <div class="col-12 col-md-2">
                                    <p class="mb-0"><b>{{ $mt->getId() }}</b></p>
                                </div>
                                <div class="col-12 col-md-3">
                                    <p class="mb-0" style="line-height: 1.25">
                                        <span class="d-block text-truncate">{{ $mt->reseller->name }}</span>
                                        <small class="text-muted">{{ $mt->transfer_orders_count }} megrendelés</small>
                                    </p>
                                </div>
                                <div class="col-12 col-md-2 text-md-right">
                                    <p class="font-weight-semibold mb-0">@money($mt->amount) Ft</p>
                                </div>
                                <div class="col-12 col-md-2 text-md-center">
                                    <p class="mb-0">{{ $mt->created_at->format('Y.m.d H:i') }}</p>
                                </div>
                                <div class="col-12 col-md-2 text-md-center">
                                    <span class="font-weight-semibold {{ $mt->getTextColorClass() }}">{{ $mt->getStatusText() }}</span>
                                </div>
                                <div class="col-12 col-md-1">
                                    <a href="{{ action('MoneyTransferController@show', $mt) }}" class="btn btn-muted">
                                    <span class="icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                             fill="currentColor"
                                             class="bi bi-box-arrow-in-up-right" viewBox="0 0 16 16">
                                              <path fill-rule="evenodd"
                                                    d="M6.364 13.5a.5.5 0 0 0 .5.5H13.5a1.5 1.5 0 0 0 1.5-1.5v-10A1.5 1.5 0 0 0 13.5 1h-10A1.5 1.5 0 0 0 2 2.5v6.636a.5.5 0 1 0 1 0V2.5a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v10a.5.5 0 0 1-.5.5H6.864a.5.5 0 0 0-.5.5z"/>
                                              <path fill-rule="evenodd"
                                                    d="M11 5.5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793l-8.147 8.146a.5.5 0 0 0 .708.708L10 6.707V10.5a.5.5 0 0 0 1 0v-5z"/>
                                        </svg>
                                    </span>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="lead font-weight-bold">Új átutalás rögzítéséhez kattints az alábbi gombra.</p>
                        <p class="mb-0"><a href="{{ action('MoneyTransferController@chooseReseller') }}" class="btn btn-sm btn-teal">Átutalás rögzítése</a></p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
