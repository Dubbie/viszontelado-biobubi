@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row align-items-baseline">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Hívandók</h1>
            </div>
        </div>

{{--        <div id="filter-order">--}}
{{--            <p class="mb-0">--}}
{{--                <small>Szűrés</small>--}}
{{--            </p>--}}
{{--            <form id="form-customers-filter">--}}
{{--                <div class="form-row align-items-end">--}}
{{--                    <div class="col-xl">--}}
{{--                        <div class="form-group">--}}
{{--                            <label for="filter-query">Keresett kifejezés</label>--}}
{{--                            <input type="text" id="filter-query" name="filter-query"--}}
{{--                                   class="form-control form-control-sm"--}}
{{--                                   value="@if(array_key_exists('query', $filter)) {{ $filter['query'] }} @endif">--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    @if(Auth::user()->admin)--}}
{{--                        <div class="col-xl-3 col-lg-5 col-md-5">--}}
{{--                            <div class="form-group">--}}
{{--                                <label for="filter-reseller">Viszonteladó</label>--}}
{{--                                <select name="filter-reseller" id="filter-reseller"--}}
{{--                                        class="custom-select custom-select-sm">--}}
{{--                                    <option value="">Saját ügyfeleim</option>--}}
{{--                                    @foreach($resellers as $reseller)--}}
{{--                                        <option value="{{ $reseller->id }}"--}}
{{--                                                @if(array_key_exists('reseller', $filter) && $filter['reseller'] == $reseller->id) selected @endif>{{ $reseller->name }}</option>--}}
{{--                                    @endforeach--}}
{{--                                    <option value="ALL" @if(array_key_exists('reseller', $filter) && $filter['reseller'] == 'ALL') selected @endif>Összes viszonteladó</option>--}}
{{--                                </select>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    @endif--}}
{{--                    <div class="col-xl-auto col-lg-2 col-md-3">--}}
{{--                        <div class="form-group">--}}
{{--                            <button type="submit" class="btn btn-sm btn-block btn-success">Szűrés</button>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </form>--}}
{{--        </div>--}}

        @if(count($calls) > 0)
            <div class="card card-body">
                <table class="table table-sm table-borderless">
                    <thead>
                        <tr>
                            <th><small>Ügyfél</small></th>
                            <th><small>Telefonszám</small></th>
                            <th class="text-center"><small>Határidő lejárta</small></th>
                            <th class="text-center"><small>Felhívva</small></th>
                            <th class="text-right"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @php /** @var App\CustomerCall $call */ @endphp
                    @foreach($calls as $call)
                        <tr>
                            <td class="align-middle">
                                <a href="{{ action([\App\Http\Controllers\CustomerController::class, 'show'], ['customerId' => $call->customer->id]) }}" class="text-decoration-none text-dark mb-0">
                                    <span class="d-block font-weight-bold">{{ $call->customer->getFormattedName() }}</span>
                                    <span class="d-block text-muted">{{ $call->customer->email }}</span>
                                </a>
                            </td>
                            <td class="align-middle">{{ $call->customer->phone }}</td>
                            <td class="align-middle text-center">
                                <p class="mb-0 has-tooltip @if($call->isOverdue()) text-danger font-weight-bold @endif"
                                   data-toggle="tooltip"
                                   title="{{ $call->due_date->format('Y.m.d H:i:s') }}">{{ $call->getRemainingTime() }}</p>
                            </td>
                            <td class="align-middle text-center">{{ $call->called_at ? $call->called_at->format('Y.m.d H:i:s') : '-' }}</td>
                            <td class="align-middle text-right">
                                @if(!$call->called_at)
                                    <a href="{{ action([\App\Http\Controllers\CustomerCallController::class, 'complete'], ['callId' => $call->id]) }}" class="btn btn-sm btn-success">
                                    <span class="bi bi-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                          <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                                        </svg>
                                    </span>
                                        <span>Felhívtam</span>
                                    </a>
                                @else
                                    <a href="{{ action([\App\Http\Controllers\CustomerCallController::class, 'uncomplete'], ['callId' => $call->id]) }}" class="btn btn-sm btn-outline-secondary">
                                    <span class="bi bi-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check" viewBox="0 0 16 16">
                                          <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022z"/>
                                        </svg>
                                    </span>
                                        <span>Mégse hívtam</span>
                                    </a>
                                @endif
                                <a href="{{ action([\App\Http\Controllers\CustomerCallController::class, 'delete'], ['callId' => $call->id]) }}"
                                   class="btn btn-sm btn-muted has-tooltip"
                                    data-toggle="tooltip"
                                    title="Hívandó törlése">
                                    <span class="bi bi-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x" viewBox="0 0 16 16">
                                          <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                                        </svg>
                                    </span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="card card-body">
                <div class="row align-items-center">
                    <div class="col-12 col-md-3">
                        <img src="{{ url('storage/img/empty.png') }}" class="d-block w-100" alt="Üres lista ikon">
                    </div>
                    <div class="col">
                        <p class="lead">Jelenleg nincs egy felhívandó ügyfeled sem.<br>Aggodalomra semmi ok, amint érkezik új ügyfél itt fogod látni, ha hívnod kell!</p>
                        <a href="https://biobubi.hu/" target="_blank" class="btn btn-sm btn-teal">Új rendelés leadása</a>
                    </div>
                </div>
            </div>
        @endif

        <div class="paginate mt-5">{{ $calls->withQueryString()->links() }}</div>
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
