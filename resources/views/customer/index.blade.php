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
                <table class="table table-sm table-striped table-borderless">
                    <thead>
                        <tr>
                            <th>Név</th>
                            <th>Telefonszám</th>
                            <th>Város</th>
                            <th class="text-center">Utolsó vásárlás</th>
                            <th class="text-right"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php /** @var App\Customer $customer */ @endphp
                        @foreach($customers as $customer)
                            <tr class="align-middle">
                                <td>
                                    <p class="mb-0">
                                        <span class="d-block font-weight-bold">{{ $customer->getFormattedName() }}</span>
{{--                                        <span class="d-block text-muted">{{ $customer->email }}</span>--}}
                                    </p>
                                </td>
                                <td>{{ $customer->phone }}</td>
                                <td>{{ $customer->city }}</td>
                                <td class="text-center">{{ $customer->getLastOrderDate()->format('Y.m.d') ?? '-' }}</td>
                                <td class="text-right">
                                    <a href="{{ action([\App\Http\Controllers\CustomerController::class, 'show'], ['customerId' => $customer->id]) }}" class="btn btn-sm btn-outline-secondary">Részletek</a>
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
