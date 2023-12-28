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
                <h1 class="font-weight-bold mb-4">Átutalás rögzítése</h1>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <div class="card card-body text-center">
                    <p>Melyik szolgáltató által exportált adatot szeretnéd feldolgozni?</p>
                    <div class="d-flex w-100 justify-content-center">
                        <a href="{{ action('SimpleController@create') }}" class="mr-4">
                            <img src="{{ url('storage/splogo.png') }}" alt="" class="d-block"
                                style="max-height:100px">
                        </a>
                        <a href="{{ action('BarionController@create') }}">
                            <img src="{{ url('storage/barion.png') }}" alt="" class="d-block"
                                style="max-height:100px">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
