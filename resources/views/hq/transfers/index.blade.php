@extends('layouts.app')

@section('content')
    <div id="transfers-page" class="container">
        <div class="row">
            <div class="col-12 col-md">
                <h1 class="font-weight-bold mb-4">Átutalások</h1>
            </div>
            @if(Auth::user()->admin)
                <div class="col-12 col-md-auto text-md-right">
                    <a href="{{ action('MoneyTransferController@create') }}" class="btn btn-teal">Átutalás
                        rögzítése</a>
                </div>
            @endif
        </div>

        <div id="filter-order">
            <p class="mb-0">
                <small>Szűrés</small>
            </p>
            <form id="form-transfers-filter">
                <div class="form-row align-items-end">
                    <div class="col-xl">
                        <div class="form-group">
                            <label for="filter-contains">Keresett kifejezés</label>
                            <input type="text" id="filter-contains" name="filter-contains"
                                   class="form-control"
                                   value="@if(array_key_exists('contains', $filter)) {{ $filter['contains'] }} @endif">
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-5 col-md-4">
                        <div class="form-group">
                            <label for="filter-status">Állapot</label>
                            <select name="filter-status" id="filter-status" class="custom-select">
                                <option value="" @if(!array_key_exists('status', $filter)) selected @endif>Mindegy
                                </option>
                                <option value="true"
                                        @if(array_key_exists('status', $filter) && $filter['status'] == 'true') selected @endif>
                                    Elutalva
                                </option>
                                <option value="false"
                                        @if(array_key_exists('status', $filter) && $filter['status'] == 'false') selected @endif>
                                    Átutalás alatt
                                </option>
                            </select>
                        </div>
                    </div>
                    @if(Auth::user()->admin)
                        <div class="col-xl-3 col-lg-5 col-md-5">
                            <div class="form-group">
                                <label for="filter-reseller">Viszonteladó</label>
                                <select name="filter-reseller" id="filter-reseller"
                                        class="custom-select">
                                    <option value="ALL"
                                            @if(array_key_exists('reseller', $filter) && $filter['reseller'] == 'ALL') selected @endif>
                                        Összes viszonteladó
                                    </option>
                                    @foreach($resellers as $reseller)
                                        <option value="{{ $reseller->id }}"
                                                @if(array_key_exists('reseller', $filter) && $filter['reseller'] == $reseller->id) selected @endif>{{ $reseller->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif
                    <div class="col-xl-auto col-lg-2 col-md-3">
                        <div class="form-group">
                            <button type="submit" class="btn btn-block btn-success">Szűrés</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="row mt-4">
            <div class="col">
                <div class="card card-body">
                    @if(count($transfers) > 0)
                        <div class="row align-items-center d-none d-md-flex no-gutters">
                            <div class="col-12 col-md-2">
                                <p class="mb-0"><small>Azonosító</small></p>
                            </div>
                            <div class="col-12 col-md-3">
                                <p class="mb-0"><small>Viszonteladó</small></p>
                            </div>
                            <div class="col-12 col-md-2 text-md-right">
                                <p class="mb-0"><small>Összeg</small></p>
                            </div>
                            <div class="col-12 col-md-1 text-md-right">
                                <p class="mb-0"><small>Jutalék</small></p>
                            </div>
                            <div class="col-12 col-md-2 text-md-center">
                                <p class="mb-0"><small>Létrehozva</small></p>
                            </div>
                            <div class="col-12 col-md-1 text-md-center">
                                <p class="mb-0"><small>Állapot</small></p>
                            </div>
                        </div>
                        @foreach($transfers as $mt)
                            <x-money-transfer :money-transfer="$mt"
                                              class="{{ $transfers->last() != $mt ? 'mb-3' : '' }}"></x-money-transfer>
                        @endforeach

                        <div class="paginate">{{ $transfers->withQueryString()->links() }}</div>
                    @else
                        <p class="lead font-weight-bold">Új átutalás rögzítéséhez kattints az alábbi gombra.</p>
                        <p class="mb-0">
                            <a href="{{ action('MoneyTransferController@create') }}"
                               class="btn btn-sm btn-teal">Átutalás rögzítése</a>
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(() => {
            $('.form-delete-transfer').on('submit', e => {
                if (!confirm('Biztosan törölni szeretnéd az átutalásról szóló rögzítést? Ez a folyamat nem visszafordítható')) {
                    e.preventDefault();
                }
            });

            $('#form-transfers-filter').submit(function () {
                var $empty_fields = $(this).find(':input').filter(function () {
                    return $(this).val() === '';
                });
                $empty_fields.prop('disabled', true);
                return true;
            });
        });
    </script>
@endsection