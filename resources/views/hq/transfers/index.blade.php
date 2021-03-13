@extends('layouts.app')

@section('content')
    <div id="transfers-page" class="container">
        <div class="row">
            <div class="col-12 col-md">
                <h1 class="font-weight-bold mb-4">Átutalások</h1>
            </div>
            <div class="col-12 col-md-auto text-md-right">
                <a href="{{ action('MoneyTransferController@chooseReseller') }}" class="btn btn-teal">Új átutalás</a>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <div class="card card-body">
                    <div class="row align-items-center">
                        <div class="col-12 col-md-2">
                            <p class="mb-0"><small>Azonosító</small></p>
                        </div>
                        <div class="col-12 col-md-3">
                            <p class="mb-0"><small>Viszonteladó</small></p>
                        </div>
                        <div class="col-12 col-md-3 text-md-right">
                            <p class="mb-0"><small>Összeg</small></p>
                        </div>
                        <div class="col-12 col-md-3 text-md-center">
                            <p class="mb-0"><small>Állapot</small></p>
                        </div>
                    </div>
                    @php /** @var \App\MoneyTransfer $mt */ @endphp
                    @foreach($transfers as $mt)
                        <div class="row align-items-center">
                            <div class="col-12 col-md-2">
                                <p class="mb-0"><b>#BBT-{{ str_pad($mt->id, 5, '0', STR_PAD_LEFT) }}</b></p>
                            </div>
                            <div class="col-12 col-md-3">
                                <p class="mb-0" style="line-height: 1.25">
                                    <span class="d-block">{{ $mt->reseller->name }}</span>
                                    <small class="text-muted">{{ $mt->transfer_orders_count }} megrendelés</small>
                                </p>
                            </div>
                            <div class="col-12 col-md-3 text-md-right">
                                <span class="h3 mb-0">@money($mt->amount)<small
                                        class="font-weight-bold ml-1">Ft</small></span>
                            </div>
                            <div class="col-12 col-md-3 text-md-center">
                                <span>{{ $mt->completed_at ? 'Elutalva' : 'Utalás alatt' }}</span>
                            </div>
                            <div class="col-12 col-md-1">
                                <a href="#!" class="btn btn-muted">
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
                </div>
            </div>
        </div>
    </div>
@endsection
