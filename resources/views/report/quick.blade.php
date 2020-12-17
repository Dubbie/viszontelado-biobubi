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
            <div class="col-xl-9">
                <div class="card card-body mb-4">
                    <div>
                        <h5 class="font-weight-bold mb-1">Bevételek a megrendelésekből</h5>
                    </div>
                    <p class="mb-4">
                        <span id="income-sum" class="font-weight-bold text-success h5 mb-4">Betöltés alatt...</span>
                    </p>
                    <canvas id="income-chart" width="100" height="50"></canvas>
                </div>
            </div>

            <div class="col-xl-3">
                <div class="card card-body mb-4">
                    <p class="h5 font-weight-bold mb-2">Kiszállítva</p>
                    <p id="deliveries-count" class="h3 text-muted mb-0">Betöltés alatt...</p>
                </div>

                <div class="card card-body">
                    <div class="row mb-2">
                        <div class="col">
                            <h5 class="font-weight-bold mb-0">Kiadások</h5>
                        </div>
                        <div class="col-xl-auto">
                            <span class="has-tooltip" data-toggle="tooltip" title="Új kiadás hozzáadaása">
                                <button type="button" class="btn btn-sm btn-outline-secondary p-0"
                                        data-toggle="modal" data-target="#newExpenseModal">
                                    <span class="icon">
                                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-plus" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                          <path fill-rule="evenodd" d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"></path>
                                        </svg>
                                    </span>
                                </button>
                            </span>
                        </div>
                    </div>
                    <p id="expense-sum" class="lead mb-0 text-muted">Betöltés alatt...</p>
                    <hr>
                    <div id="expense-container"></div>
                </div>
            </div>
        </div>
    </div>

    @include('modal.new-expense')
@endsection