@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Központi pénzügyek</h1>
            </div>
            <div class="col-auto">
                <div class="d-flex">
                    {{--<span class="has-tooltip mr-2 mb-0" data-toggle="tooltip" title="Új központi bevétel hozzáadaása">--}}
                        {{--<button type="button" class="btn btn-sm btn-outline-success"--}}
                                {{--data-toggle="modal" data-target="#newHqIncome">--}}
                            {{--<span>Új központi bevétel</span>--}}
                        {{--</button>--}}
                    {{--</span>--}}
                    <span class="has-tooltip mb-0" data-toggle="tooltip" title="Új központi kiadás hozzáadaása">
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                data-toggle="modal" data-target="#newHqExpense">
                            <span>Új központi kiadás</span>
                        </button>
                    </span>
                </div>
            </div>
        </div>

        <div class="card card-body">
            <div class="row">
                <div class="col">
                    <p id="hqf-finance-range" class="d-inline-block btn btn-sm btn-outline-secondary mb-4">
                        <span id="hqf-finance-range-label"></span>
                        <span class="icon icon-sm">
                            <i class="fas fa-angle-down"></i>
                        </span>
                    </p>
                </div>
                <div class="col-auto text-right">
                    <div class="d-flex">
                        <div class="form-group d-flex align-items-center mb-0">
                            <label for="cf-mode" class="mr-1 mb-0">Nézet: </label>
                            <select name="cf-mode" id="cf-mode"
                                    class="custom-select custom-select-sm font-weight-bold pl-1 border-0">
                                <option value="DAILY" selected>Napi</option>
                                <option value="MONTHLY" disabled="">Havi</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div id="finance-table-container">
                @include('inc.finance.hq-daily')
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <div class="card card-body">
                    <div class="d-flex align-items-center">
                            <span class="icon bg-success-pastel text-success rounded-circle mr-3"
                                  style="width: 48px; height: 48px;">
                                <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-arrow-down-circle-fill"
                                     fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                  <path fill-rule="evenodd"
                                        d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v5.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V4.5z"></path>
                                </svg>
                            </span>
                        <div class="text">
                            <p class="font-weight-bold mb-0">Bevétel</p>
                            <p id="hq-income-sum"
                               class="h1 font-weight-bold mb-0 text-success-pastel">{{ resolve('App\Subesz\MoneyService')->getFormattedMoney($hqFinanceData['data']['incomes']->sum('gross_value')) }}
                                Ft</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card card-body">
                    <div class="d-flex align-items-center">
                            <span class="icon bg-danger-pastel text-danger rounded-circle mr-3"
                                  style="width: 48px; height: 48px;">
                                <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-arrow-up-circle-fill"
                                     fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                          d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-7.5 3.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708l3-3a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707V11.5z"></path>
                                </svg>
                            </span>
                        <div class="text">
                            <p class="font-weight-bold mb-0">Kiadások</p>
                            <p id="hq-expense-sum"
                               class="h1 font-weight-bold mb-0 text-danger-pastel">{{ resolve('App\Subesz\MoneyService')->getFormattedMoney($hqFinanceData['data']['expenses']->sum('gross_value')) }}
                                Ft</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card card-body">
                    <div class="d-flex align-items-center">
                            <span class="icon bg-info-pastel text-info rounded-circle mr-3"
                                  style="width: 48px; height: 48px;">
                                <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-cash-stack"
                                     fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                  <path d="M14 3H1a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1h-1z"></path>
                                  <path fill-rule="evenodd"
                                        d="M15 5H1v8h14V5zM1 4a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1V5a1 1 0 0 0-1-1H1z"></path>
                                  <path d="M13 5a2 2 0 0 0 2 2V5h-2zM3 5a2 2 0 0 1-2 2V5h2zm10 8a2 2 0 0 1 2-2v2h-2zM3 13a2 2 0 0 0-2-2v2h2zm7-4a2 2 0 1 1-4 0 2 2 0 0 1 4 0z"></path>
                                </svg>
                            </span>
                        <div class="text">
                            <p class="font-weight-bold mb-0">Fizetendő ÁFA</p>
                            <p id="hq-tax-sum"
                               class="h1 font-weight-bold text-info-pastel mb-0">{{ resolve('App\Subesz\MoneyService')->getFormattedMoney($hqFinanceData['data']['incomes']->sum('tax_value')) }}
                                Ft</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('modal.new-hq-expense')
@endsection
