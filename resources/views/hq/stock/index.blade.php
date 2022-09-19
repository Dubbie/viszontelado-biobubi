@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Központi készlet</h1>
            </div>
            @if(Auth::user()->admin)
                <div class="col text-right">
                    <a href="{{ action('CentralStockController@history') }}"
                       class="btn btn-muted">Történet</a>
                    <a href="#stockMovement" data-toggle="modal" data-target="#stockMovement"
                       class="btn btn-teal shadow-sm">Készlet mozgatása</a>
                </div>
            @endif
        </div>

        <div class="card card-body p-md-5 mb-4">
            <div id="cs-list">{!! resolve('App\Subesz\StockService')->getCentralStockHTML() !!}</div>
        </div>
    </div>

    @include('modal.stock.stock-movement')
@endsection