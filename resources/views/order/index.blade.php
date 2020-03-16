@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <h3 class="font-weight-bold mb-4">Megrendelések</h3>
            </div>
            @if(Auth()->user()->admin)
                <div class="col text-right">
                    <a href="{{ action('ShoprenterController@updateOrders') }}"
                       class="btn btn-sm btn-outline-secondary">Megrendelések frissítése</a>
                </div>
            @endif
        </div>

        @if(Auth()->user()->admin && count(Auth()->user()->zips) == 0)
            <div class="alert alert-info">
                <p class="mb-0">Ez a fiók adminisztrátori jogkörrel rendelkezik és nincs hozzárendelve irányítószám,
                    ezért az összes megrendelést látja.</p>
            </div>
        @endif

        @if(Auth()->user()->admin)
            <p class="mb-0">
                <small>Szűrés</small>
            </p>
            <form class="mb-4">
                <div class="row align-items-end">
                    <div class="col-xl-4">
                        <div class="form-group mb-0">
                            <label for="filter-query">Keresett kifejezés</label>
                            <input type="text" id="filter-query" name="filter-query" class="form-control">
                        </div>
                    </div>
                    <div class="col-xl-3">
                        <div class="form-group mb-0">
                            <label for="filter-status">Állapot</label>
                            <select name="filter-status" id="filter-status" class="custom-select">
                                <option value="">Mindegy</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->name }}">{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-4">
                        <div class="form-group mb-0">
                            <label for="filter-reseller">Viszonteladó</label>
                            <select name="filter-reseller" id="filter-reseller" class="custom-select">
                                <option value="">Saját megrendeléseim</option>
                                @foreach($resellers as $reseller)
                                    <option value="{{ $reseller->id }}">{{ $reseller->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <button type="submit" class="btn btn-success">Szűrés</button>
                    </div>
                </div>
            </form>
        @endif

        <div class="card card-body">
            <table class="table table-sm table-borderless mb-0">
                <thead>
                <tr>
                    <th scope="col">
                        <small class="font-weight-bold">Ügyfél</small>
                    </th>
                    <th scope="col">
                        <small class="font-weight-bold">Állapot</small>
                    </th>
                    <th scope="col">
                        <small class="font-weight-bold">Város</small>
                    </th>
                    <th scope="col">
                        <small class="font-weight-bold">Kezdő dátum</small>
                    </th>
                    <th scope="col" class="text-right">
                        <small class="font-weight-bold">Összesen</small>
                    </th>
                    <td></td>
                </tr>
                </thead>
                <tbody>
                @foreach($orders as $order)
                    <tr>
                        {{--<td class="align-middle">--}}
                        {{--<div class="custom-control custom-checkbox">--}}
                        {{--<input type="checkbox" class="custom-control-input ch-order-select"--}}
                        {{--id="ch-select-order-{{ $order->inner_id }}">--}}
                        {{--<label class="custom-control-label" for="ch-select-order-{{ $order->inner_id }}"></label>--}}
                        {{--</div>--}}
                        {{--</td>--}}
                        <td>
                            <p class="mb-0">{{ $order->lastname }} {{ $order->firstname }}
                                <small class="d-block">{{ $order->email }}</small>
                            </p>
                        </td>
                        <td class="align-middle"><p class="mb-0">{{ $order->status_text }}</p></td>
                        <td class="align-middle"><p class="mb-0">{{ $order->getFormattedAddress() }}</p></td>
                        <td class="align-middle"><p class="mb-0">{{ $order->created_at->format('Y. m. d. H:i') }}</p>
                        </td>
                        <td class="text-right align-middle"><p
                                    class="mb-0">{{ number_format($order->total_gross, 0, '.', ' ') }} Ft</p>
                        </td>
                        <td class="align-middle text-right">
                            <a href="{{ action('OrderController@show', ['orderId' => $order->inner_resource_id]) }}"
                               class="btn-icon">
                                <span class="icon">
                                    <i class="fas fa-expand"></i>
                                </span>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            $('form').submit(function () {
                var $empty_fields = $(this).find(':input').filter(function () {
                    return $(this).val() === '';
                });
                $empty_fields.prop('disabled', true);
                return true;
            });
        });
    </script>
@endsection