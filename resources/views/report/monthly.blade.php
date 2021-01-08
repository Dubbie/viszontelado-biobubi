@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Havi riportok</h1>
            </div>
        </div>

        @if(Auth::user()->reports()->count() > 0)
            <div class="row">
                <div class="col-xl-10">
                    <div class="card card-body mb-4">
                       @php
                           /** @var  \App\Report  $report **/
                            /** @var  \App\ReportProducts  $rp */
                           $report = Auth::user()->reports[11]
                       @endphp

                        <h2 class="font-weight-bold mb-4">{{ \Illuminate\Support\Str::title($report->created_at->translatedFormat('Y F')) }}</h2>
                        <div class="row">
                            <div class="col-12 col-lg-4 mb-4">
                                <div class="card card-body border shadow-none">
                                    <div class="row align-items-center">
                                        <div class="col-4">
                                            <span class="icon icon-lg bg-success-pastel rounded-circle">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-plus text-success" viewBox="0 0 16 16">
                                                  <path
                                                      d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="col-8">
                                            <p class="text-muted mb-0">Bevétel</p>
                                            <p class="h5 font-weight-bold mb-0">{{ resolve('App\Subesz\MoneyService')->getFormattedMoney($report->gross_income) }} Ft</p>
                                            @if($report->hasPrevious())
                                            <p class="mb-0 @if($report->getIncomeDifference() > 0) text-success-pastel @else text-danger-pastel @endif">
                                                <small class="font-weight-bold">{{ $report->getIncomeDifferencePercent() }}</small>
                                            </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4 mb-4">
                                <div class="card card-body border shadow-none">
                                    <div class="row align-items-center">
                                        <div class="col-4">
                                            <span class="icon icon-lg bg-danger-pastel rounded-circle">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-dash text-danger" viewBox="0 0 16 16">
                                                    <path d="M4 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 4 8z"/>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="col-8">
                                            <p class="text-muted mb-0">Kiadások</p>
                                            <p class="h5 mb-0 font-weight-bold">{{ resolve('App\Subesz\MoneyService')->getFormattedMoney($report->gross_expense) }} Ft</p>
                                            @if($report->hasPrevious())
                                                <p class="mb-0 @if($report->getExpenseDifference() <= 0) text-success-pastel @else text-danger-pastel @endif">
                                                    <small class="font-weight-bold">{{ $report->getExpenseDifferencePercent() }}</small>
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <div class="card card-body border shadow-none">
                                    <div class="row align-items-center">
                                        <div class="col-4">
                                            <span class="icon icon-lg bg-info-pastel rounded-circle">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-truck text-primary" viewBox="0 0 16 16">
                                                  <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="col-8">
                                            <p class="text-muted mb-0">Kiszállítások</p>
                                            <p class="h5 mb-0 font-weight-bold">{{ resolve('App\Subesz\MoneyService')->getFormattedMoney($report->delivered_orders) }} cím</p>
                                            @if($report->hasPrevious())
                                                <p class="mb-0 @if($report->getDeliveriesDifference() > 0) text-success-pastel @else text-danger-pastel @endif">
                                                    <small class="font-weight-bold">{{ $report->getDeliveriesDifferencePercent() }}</small>
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-body mt-4">
                        <h5>Rendelt termékek a hónapban</h5>

                        @if($report->reportProducts()->count() > 0)
                            @foreach($report->reportProducts as $rp)
                                <div class="row">
                                    <div class="col">{{ $rp->product_sku }}</div>
                                    <div class="col">{{ $rp->product_qty }} db</div>
                                </div>
                            @endforeach
                        @else
                            <p class="lead">Nincs információ.</p>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <p class="lead">Nincsenek még generálva elmentett riportok.</p>
        @endif
    </div>
@endsection
