@extends('layouts.app')

@section('content')
    @php /** @var \App\Customer $customer */ @endphp
    <div class="container">
        <p class="mb-0">
            <a href="{{ action('CustomerController@index') }}" class="btn-muted font-weight-bold text-decoration-none">
                        <span class="icon icon-sm">
                            <i class="fas fa-arrow-left"></i>
                        </span>
                <span>Vissza az ügyfelekhez</span>
            </a>
        </p>
        <div class="row align-items-baseline">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Ügyfél részletei</h1>
            </div>
        </div>

        <div class="card card-body">
            <div class="row">
                <div class="col-lg-3">
                    <h5 class="font-weight-bold mb-2">Elérhetőség</h5>
                    <p class="text-muted">Az ügyfél első rendelése alapján megadott elérhetőségek.</p>
                </div>
                <div class="col-lg-9">
                    <p class="h3 font-weight-bold mb-0">{{ $customer->getFormattedName() }}</p>
                    <p class="h5 mb-3 text-muted">{{ $customer->email }}</p>

                    {{--Telefonszám--}}
                    <div class="d-flex align-items-center">
                        <span class="icon text-muted mr-2" style="width: 20px; height: 20px;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                              <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                            </svg>
                        </span>
                        <p class="font-weight-bold mb-0">{{ $customer->phone }}</p>
                    </div>

                    {{--Cím--}}
                    <div class="d-flex align-items-center">
                        <span class="icon text-muted mr-2" style="width: 20px; height: 20px;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        <p class="font-weight-bold mb-0">{{ $customer->getFormattedAddress() }}</p>
                    </div>

                    {{-- Mikor rendelt utoljára? --}}
                    <div class="d-flex align-items-center">
                        <span class="icon text-muted mr-2" style="width: 20px; height: 20px;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        <p class="mb-0">Utoljára <b class="has-tooltip" data-toggle="tooltip" title="{{ $customer->getLastOrderDate() }}">{{ $customer->getLastOrderTimeAgo() }}</b> rendelt.</p>
                    </div>
                </div>
            </div>

            {{-- Megrendelések --}}
            <div class="row mt-5">
                <div class="col-lg-3">
                    <h5 class="font-weight-bold mb-2">Megrendelései</h5>
                    <p class="text-muted">Az ügyfél összes megrendelése e-mail címe alapján.</p>
                </div>
                <div class="col-lg-9">
                    @if($customer->orders()->count() > 0)
                        @php /** @var \App\Order $localOrder */ @endphp
                        @foreach($customer->orders as $localOrder)
                            <p>{{ $localOrder }}</p>
                        @endforeach
                    @else
                        <p>Még nem rendelt az ügyfél.</p>
                    @endif
                </div>
            </div>
        </div>
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
