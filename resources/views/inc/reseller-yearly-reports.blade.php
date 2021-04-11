@if($selectedReports->count() > 0)
    <div class="row align-items-center">
        <div class="col">
            <h1 class="font-weight-bold mb-4">Éves riportok</h1>
        </div>
        <div class="col-md-auto">
            <form action="">
                <div class="form-group">
                    <label for="date" class="sr-only">Éves riport</label>
                    <select name="year" id="year" class="custom-select">
                        @foreach(\App\Http\Controllers\ReportController::allYears() as $item)
                            <option value="{{ $item->year }}"
                                    @if($selectedYear && $selectedYear == $item->year) selected @endif>{{ $item->year }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card card-body border-bottom border-secondary rounded-0 pr-5 pl-5 pb-3 pt-2 mb-4">
                <div class="row mb-0 text-center">
                    <div class="col-2">
                        <p class="font-weight-bold mb-1">Dátum</p>
                    </div>
                    <div class="col-2">
                        <p class="font-weight-bold mb-1">Bevétel</p>
                    </div>
                    <div class="col-2">
                        <p class="font-weight-bold mb-1">Kiadás</p>
                    </div>
                    <div class="col-2">
                        <p class="font-weight-bold mb-1">Kiszállítások</p>
                    </div>
                    <div class="col-2">
                        <p class="font-weight-bold mb-1">Átlag bevétel / Cím</p>
                    </div>
                    <div class="col-2">
                        <p class="font-weight-bold mb-1">Átl. Benzink. / Cím</p>
                    </div>
                </div>
            </div>
            @foreach($selectedReports as $report)

                <div class="card card-body border-bottom border-secondary rounded-0 pr-5 pl-5 pt-0 pb-3 mb-4">
                    <div class="row text-center">
                        {{--dátum--}}
                        <div class="col-2">
                            <p class="h5 font-weight-bold">{{$report->created_at->translatedFormat('Y/m')}}</p>
                        </div>
                        {{--bevétel--}}
                        <div class="col-2">
                            <p
                                class="h5 font-weight-bold mb-0">{{ resolve('App\Subesz\MoneyService')->getFormattedMoney($report->gross_income) }}
                                Ft</p>
                            @if($report->hasPrevious())
                                <p
                                    class="mb-0 @if($report->getIncomeDifference() > 0) text-success-pastel @else text-danger-pastel @endif">
                                    <small class="font-weight-bold">{{ $report->getIncomeDifferencePercent() }}</small>
                                </p>
                            @endif
                        </div>
                        {{--kiadások--}}
                        <div class="col-2">
                            <p
                                class="h5 mb-0 font-weight-bold">{{ resolve('App\Subesz\MoneyService')->getFormattedMoney($report->gross_expense) }}
                                Ft</p>
                            @if($report->hasPrevious())
                                <p
                                    class="mb-0 @if($report->getExpenseDifference() <= 0) text-success-pastel @else text-danger-pastel @endif">
                                    <small class="font-weight-bold">{{ $report->getExpenseDifferencePercent() }}</small>
                                </p>
                            @endif
                        </div>
                        {{--kiszállítások--}}
                        <div class="col-2">
                            <p
                                class="h5 mb-0 font-weight-bold">{{ resolve('App\Subesz\MoneyService')->getFormattedMoney($report->delivered_orders) }}
                                cím</p>
                            @if($report->hasPrevious())
                                <p
                                    class="mb-0 @if($report->getDeliveriesDifference() > 0) text-success-pastel @else text-danger-pastel @endif">
                                    <small class="font-weight-bold">{{ $report->getDeliveriesDifferencePercent() }}</small>
                                </p>
                            @endif
                        </div>
                        {{--bevétel címenként--}}
                        <div class="col-2">
                            <p
                                class="h5 mb-0 font-weight-bold">{{ resolve('App\Subesz\MoneyService')->getFormattedMoney($report->getAvgIncomeByDeliveries()) }}
                                Ft</p>
                        </div>
                        {{--benzinköltség--}}
                        <div class="col-2">
                            <p
                                class="h5 mb-0 font-weight-bold">{{ resolve('App\Subesz\MoneyService')->getFormattedMoney($report->getDeliveryExpenseByAddress()) }}
                                Ft</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@else
    <div class="row">
        <div class="col">
            <h1 class="font-weight-bold mb-4">Éves riport</h1>
        </div>
    </div>
    <p class="lead">Nincsenek még generálva elmentett riportok.</p>
@endif
