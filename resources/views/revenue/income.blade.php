@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Pénzügy</h1>
            </div>
        </div>

        <p id="income-range" class="d-inline-block btn btn-outline-secondary mb-4">
            <span id="income-range-label"></span>
            <span class="icon">
                <i class="fas fa-angle-down"></i>
            </span>
        </p>
        <div class="row">
            <div class="col-xl-8">
                <div class="card card-body mb-4">
                    <div>
                        <h5 class="font-weight-bold mb-1">Bevételek a megrendelésekből</h5>
                    </div>
                    <p class="mb-4">
                        <span id="income-sum" class="font-weight-bold text-success h5 mb-4">Betöltés alatt...</span>
                    </p>
                    <canvas id="income-chart" width="100" height="50"></canvas>
                </div>

                <div class="card card-body">
                    <div class="row align-items-baseline">
                        <div class="col-auto">
                            <h2 class="font-weight-bold mb-0">Profit</h2>
                        </div>
                        <div class="col">
                            <h2 id="profit" class="text-muted mb-0">Betöltés alatt...</h2>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-xl-4">
                <div class="card card-body">
                    <div class="row mb-2">
                        <div class="col">
                            <h5 class="font-weight-bold mb-0">Kiadások</h5>
                            <p id="expense-sum" class="mb-0 text-muted">Betöltés alatt...</p>
                        </div>
                        <div class="col-xl-auto">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-toggle="modal" data-target="#newExpenseModal">
                                <span>Új kiadás</span>
                            </button>
                        </div>
                    </div>
                    <div id="expense-container"></div>
                </div>

                @if(Auth()->user()->admin)
                    <div class="card card-body bg-dark text-white mt-4">
                        <h5 class="font-weight-bold">Benji kiszállításai</h5>
                        <div id="deliveries-container"><span class="text-white-50">Betöltés alatt...</span></div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @include('modal.new-expense')
@endsection