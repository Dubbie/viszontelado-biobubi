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
            <div
                class="row border-bottom mb-0 text-center align-items-end px-3 py-2">
                <div class="col-2">
                    <p class="font-weight-bold text-muted mb-1"><small>Dátum</small></p>
                </div>
                <div class="col-2">
                    <p class="font-weight-bold text-muted mb-1"><small>Bevétel</small></p>
                </div>
                <div class="col-2">
                    <p class="font-weight-bold text-muted mb-1"><small>Kiadás</small></p>
                </div>
                <div class="col-2">
                    <p class="font-weight-bold text-muted mb-1"><small>Kiszállítások</small></p>
                </div>
                <div class="col-2">
                    <p class="font-weight-bold text-muted mb-1"><small>Átlag bevétel / Cím</small></p>
                </div>
                <div class="col-2">
                    <p class="font-weight-bold text-muted mb-1"><small>Átl. Benzink. / Cím</small></p>
                </div>
            </div>
            @foreach($selectedReports as $report)

                <div
                    class="@if($selectedReports->last() != $report) border-bottom @else mb-2 @endif row text-center px-3 py-2">
                    {{--dátum--}}
                    <div class="col-2">
                        <p class="h5 mb-0 font-weight-bold">{{$report->created_at->translatedFormat('Y/m')}}</p>
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
                                <small
                                    class="font-weight-bold">{{ $report->getDeliveriesDifferencePercent() }}</small>
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
