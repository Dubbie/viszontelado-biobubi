@extends('layouts.app')

@section('content')
    <div id="transfers-page" class="container">
        <div class="row">
            <div class="col-12 col-md">
                <h1 class="font-weight-bold mb-4">Átutalások</h1>
            </div>
            <div class="col-12 col-md-auto text-md-right">
                <a href="{{ action('MoneyTransferController@chooseReseller') }}" class="btn btn-teal">Átutalás
                    rögzítése</a>
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
                        @foreach($transfers as $mt)
                            <x-money-transfer :money-transfer="$mt"
                                              class="{{ $transfers->last() != $mt ? 'mb-3' : '' }}"></x-money-transfer>
                        @endforeach

                        <div class="paginate">{{ $transfers->withQueryString()->links() }}</div>
                    @else
                        <p class="lead font-weight-bold">Új átutalás rögzítéséhez kattints az alábbi gombra.</p>
                        <p class="mb-0">
                            <a href="{{ action('MoneyTransferController@chooseReseller') }}"
                               class="btn btn-sm btn-teal">Átutalás rögzítése</a>
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
