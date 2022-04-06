@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row align-items-baseline">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Összes ügyfél</h1>
            </div>
        </div>

        <div id="filter-order">
            <p class="mb-0">
                <small>Szűrés</small>
            </p>
            <form id="form-customers-filter">
                <div class="form-row align-items-end">
                    <div class="col-xl">
                        <div class="form-group">
                            <label for="filter-query">Keresett kifejezés</label>
                            <input type="text" id="filter-query" name="filter-query"
                                   class="form-control form-control-sm"
                                   value="@if(array_key_exists('query', $filter)) {{ $filter['query'] }} @endif">
                        </div>
                    </div>
                    @if(Auth::user()->admin)
                        <div class="col-xl-3 col-lg-5 col-md-5">
                            <div class="form-group">
                                <label for="filter-reseller">Viszonteladó</label>
                                <select name="filter-reseller" id="filter-reseller"
                                        class="custom-select custom-select-sm">
                                    <option value="">Saját ügyfeleim</option>
                                    @foreach($resellers as $reseller)
                                        <option value="{{ $reseller->id }}"
                                                @if(array_key_exists('reseller', $filter) && $filter['reseller'] == $reseller->id) selected @endif>{{ $reseller->name }}</option>
                                    @endforeach
                                    <option value="ALL" @if(array_key_exists('reseller', $filter) && $filter['reseller'] == 'ALL') selected @endif>Összes viszonteladó</option>
                                </select>
                            </div>
                        </div>
                    @endif
                    <div class="col-xl-auto col-lg-2 col-md-3">
                        <div class="form-group">
                            <button type="submit" class="btn btn-sm btn-block btn-success">Szűrés</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        @if(count($customers) > 0)
            <div class="card card-body">
{{--                <table class="table table-sm table-striped table-borderless">--}}
{{--                    <thead>--}}
{{--                        <tr>--}}
{{--                            <th>Név</th>--}}
{{--                            <th>Telefonszám</th>--}}
{{--                            <th>Város</th>--}}
{{--                            <th class="text-center">Utolsó vásárlás</th>--}}
{{--                            <th class="text-right"></th>--}}
{{--                        </tr>--}}
{{--                    </thead>--}}
{{--                    <tbody>--}}
{{--                        @php /** @var App\Customer $customer */ @endphp--}}
{{--                        @foreach($customers as $customer)--}}
{{--                            <tr class="align-middle">--}}
{{--                                <td>--}}
{{--                                    <p class="mb-0">--}}
{{--                                        <span class="d-block font-weight-bold">{{ $customer->getFormattedName() }}</span>--}}
{{--                                        <span class="d-block text-muted">{{ $customer->email }}</span>--}}
{{--                                    </p>--}}
{{--                                </td>--}}
{{--                                <td>{{ $customer->phone }}</td>--}}
{{--                                <td>{{ $customer->city }}</td>--}}
{{--                                <td class="text-center">{{ $customer->getLastOrderDate()->format('Y.m.d') ?? '-' }}</td>--}}
{{--                                <td class="text-right">--}}
{{--                                    <a href="{{ action([\App\Http\Controllers\CustomerController::class, 'show'], ['customerId' => $customer->id]) }}" class="btn btn-sm btn-outline-secondary">Részletek</a>--}}
{{--                                </td>--}}
{{--                            </tr>--}}
{{--                        @endforeach--}}
{{--                    </tbody>--}}
{{--                </table>--}}

                @php /** @var App\Customer $customer */ @endphp
                @foreach($customers as $customer)
                    <div class="row">
                        <div class="col-12 col-lg-4">
                            <p class="mb-0">
                                <span class="d-block font-weight-bold">{{ $customer->getFormattedName() }}</span>
                                <span class="d-block font-weight-bold text-muted">{{ $customer->email }}</span>
                                <small class="d-block mt-2">Nincs megjegyzés</small>
                            </p>
                        </div>
                        <div class="col-12 col-lg-2">
                            <p class="mb-0">{{ $customer->phone }}</p>
                        </div>
                        <div class="col-6 col-lg-2">{{ $customer->city }}</div>
                        <div class="col-6 col-lg-2 has-tooltip" >
                            <p class="mb-0 d-inline-flex align-items-center">
                                <span class="icon bs-icon text-muted mr-1 has-tooltip" data-toggle="tooltip" title="Utolsó rendelés dátuma">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16">
                                      <path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022l-.074.997zm2.004.45a7.003 7.003 0 0 0-.985-.299l.219-.976c.383.086.76.2 1.126.342l-.36.933zm1.37.71a7.01 7.01 0 0 0-.439-.27l.493-.87a8.025 8.025 0 0 1 .979.654l-.615.789a6.996 6.996 0 0 0-.418-.302zm1.834 1.79a6.99 6.99 0 0 0-.653-.796l.724-.69c.27.285.52.59.747.91l-.818.576zm.744 1.352a7.08 7.08 0 0 0-.214-.468l.893-.45a7.976 7.976 0 0 1 .45 1.088l-.95.313a7.023 7.023 0 0 0-.179-.483zm.53 2.507a6.991 6.991 0 0 0-.1-1.025l.985-.17c.067.386.106.778.116 1.17l-1 .025zm-.131 1.538c.033-.17.06-.339.081-.51l.993.123a7.957 7.957 0 0 1-.23 1.155l-.964-.267c.046-.165.086-.332.12-.501zm-.952 2.379c.184-.29.346-.594.486-.908l.914.405c-.16.36-.345.706-.555 1.038l-.845-.535zm-.964 1.205c.122-.122.239-.248.35-.378l.758.653a8.073 8.073 0 0 1-.401.432l-.707-.707z"/>
                                      <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0v1z"/>
                                      <path d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5z"/>
                                    </svg>
                                </span>
                                <span>{{ $customer->getLastOrderDate()->format('Y.m.d') ?? '-' }}</span>
                            </p>
                        </div>
                        <div class="col-12 mt-1 mt-lg-0 col-lg text-lg-right">
                            <a href="{{ action([\App\Http\Controllers\CustomerController::class, 'show'], ['customerId' => $customer->id]) }}" class="btn btn-sm btn-outline-secondary">Részletek</a>
                        </div>
                    </div>
                    @if($customers->last() != $customer)
                        <hr>
                    @endif
                @endforeach
            </div>
        @else
            <div class="card card-body">
                <div class="row align-items-center">
                    <div class="col-12 col-md-3">
                        <img src="{{ url('storage/img/empty.png') }}" class="d-block w-100" alt="Üres lista ikon">
                    </div>
                    <div class="col">
                        <p class="lead">Jelenleg még nincs egy ügyfeled sem.<br>Aggodalomra semmi ok, amint érkezik megrendelésed itt fogod látni!</p>
                        <a href="https://biobubi.hu/" target="_blank" class="btn btn-sm btn-teal">Új rendelés leadása</a>
                    </div>
                </div>
            </div>
        @endif

        <div class="paginate mt-5">{{ $customers->withQueryString()->links() }}</div>
    </div>
@endsection

@section('scripts')
    {{-- Szűrő --}}
    <script>
        $(function () {
            $('#form-customers-filter').submit(function () {
                var $empty_fields = $(this).find(':input').filter(function () {
                    return $(this).val() === '';
                });
                $empty_fields.prop('disabled', true);
                return true;
            });
        });
    </script>
@endsection
