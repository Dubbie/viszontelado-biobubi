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
                        <form action="{{ action('ReportController@showMonthly') }}">
                            <div class="form-group row no-gutters">
                                <label for="date" class="font-weight-bold col-4 col-md-6 col-xl-9">Havi riport</label>
                                <select name="date" id="date" class="custom-select col-8 col-md-6 col-xl-3">
                                    @foreach(Auth::user()->reports()->orderByDesc('created_at')->get() as $report)
                                        <option value="{{ $report->created_at->format('Y-m') }}" @if($selectedReport && $selectedReport->id == $report->id) selected @endif>{{ \Illuminate\Support\Str::title($report->created_at->translatedFormat('Y F')) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </form>

                        @php
                            /** @var  \App\Report  $report **/
                            /** @var  \App\ReportProducts  $rp */
                            /** @var  \App\MarketingResult  $selectedMarketing */
                            $report = $selectedReport ?? Auth::user()->reports()->orderByDesc('created_at')->first();
                        @endphp

                        <h2 class="font-weight-bold mb-4">{{ \Illuminate\Support\Str::title($report->created_at->translatedFormat('Y F')) }}</h2>
                        <div class="row">
                            <div class="col-12 col-lg-4 mb-4 mb-md-0">
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
                            <div class="col-12 col-lg-4 mb-4 mb-md-0">
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
                            <div class="col-12 col-lg-4 mb-4 mb-md-0">
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
                            <div class="col-12 col-lg-6 mt-0 mt-lg-4 mb-4 mb-md-0">
                                <div class="card card-body border shadow-none">
                                    <div class="row align-items-center">
                                        <div class="col-3">
                                            <span class="icon icon-lg bg-success-pastel rounded-circle">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-plus text-success" viewBox="0 0 16 16">
                                                  <path
                                                      d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="col-9">
                                            <p class="text-muted mb-0">Átlag Bevétel / Cím</p>
                                            <p class="h5 mb-0 font-weight-bold">{{ resolve('App\Subesz\MoneyService')->getFormattedMoney($report->getAvgIncomeByDeliveries()) }} Ft</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6 mt-0 mt-lg-4">
                                <div class="card card-body border shadow-none">
                                    <div class="row align-items-center">
                                        <div class="col-3">
                                            <span class="icon icon-lg bg-danger-pastel rounded-circle">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-speedometer2 text-danger-pastel" viewBox="0 0 16 16">
                                                  <path d="M8 4a.5.5 0 0 1 .5.5V6a.5.5 0 0 1-1 0V4.5A.5.5 0 0 1 8 4zM3.732 5.732a.5.5 0 0 1 .707 0l.915.914a.5.5 0 1 1-.708.708l-.914-.915a.5.5 0 0 1 0-.707zM2 10a.5.5 0 0 1 .5-.5h1.586a.5.5 0 0 1 0 1H2.5A.5.5 0 0 1 2 10zm9.5 0a.5.5 0 0 1 .5-.5h1.5a.5.5 0 0 1 0 1H12a.5.5 0 0 1-.5-.5zm.754-4.246a.389.389 0 0 0-.527-.02L7.547 9.31a.91.91 0 1 0 1.302 1.258l3.434-4.297a.389.389 0 0 0-.029-.518z"/>
                                                  <path fill-rule="evenodd" d="M0 10a8 8 0 1 1 15.547 2.661c-.442 1.253-1.845 1.602-2.932 1.25C11.309 13.488 9.475 13 8 13c-1.474 0-3.31.488-4.615.911-1.087.352-2.49.003-2.932-1.25A7.988 7.988 0 0 1 0 10zm8-7a7 7 0 0 0-6.603 9.329c.203.575.923.876 1.68.63C4.397 12.533 6.358 12 8 12s3.604.532 4.923.96c.757.245 1.477-.056 1.68-.631A7 7 0 0 0 8 3z"/>
                                                </svg>
                                            </span>
                                        </div>
                                        <div class="col-9">
                                            <p class="text-muted mb-0">Átlag Benzinköltség / Cím</p>
                                            <p class="h5 mb-0 font-weight-bold">{{ resolve('App\Subesz\MoneyService')->getFormattedMoney($report->getDeliveryExpenseByAddress()) }} Ft</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
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
                                    <p class="text-muted">Nincs információ.</p>
                                @endif
                            </div>
                        </div>
                        <div class="col">
                            <div class="card card-body mt-4">
                                <h5>Marketinges eredmények</h5>

                                @if($selectedMarketing)
                                    @if($selectedMarketing->topup_amount > 0)
                                        <p class="row no-gutters mb-0">
                                            <span class="col-12 col-lg-7 text-muted">Egyenlegfeltöltés</span>
                                            <span class="col-12 col-lg-5 text-lg-right"><b>{{ resolve('App\Subesz\MoneyService')->getFormattedMoney($selectedMarketing->topup_amount) }} Ft</b></span>
                                        </p>
                                    @endif
                                    <p class="row no-gutters mb-0">
                                        <span class="col-12 col-lg-7 text-muted">Marketingre költve</span>
                                        <span class="col-12 col-lg-5 text-lg-right"><b>{{ resolve('App\Subesz\MoneyService')->getFormattedMoney($selectedMarketing->spent) }} Ft</b></span>
                                    </p>
                                    <p class="row no-gutters mb-0">
                                        <span class="col-12 col-lg-7 text-muted">Vásárlások száma</span>
                                        <span class="col-12 col-lg-5 text-lg-right"><b>{{ resolve('App\Subesz\MoneyService')->getFormattedMoney($selectedMarketing->reached) }} megrendelés</b></span>
                                    </p>
                                    <p class="row no-gutters mb-0">
                                        <span class="col-12 col-lg-9 text-muted">Átlag. költés / Vásárlások száma</span>
                                        <span class="col-12 col-lg-3 text-lg-right"><b>{{ resolve('App\Subesz\MoneyService')->getFormattedMoney(ceil($selectedMarketing->spent / $selectedMarketing->reached)) }} Ft</b></span>
                                    </p>
                                @else
                                    <p class="text-muted">Még nem került kitöltésre erre az időszakra marketinges eredmény.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <p class="lead">Nincsenek még generálva elmentett riportok.</p>
        @endif
    </div>
@endsection

@section('scripts')
    <script>
        $(() => {
            $('#date').on('change', e => {
                $(e.currentTarget).closest('form').submit();
            });
        });
    </script>
@endsection
