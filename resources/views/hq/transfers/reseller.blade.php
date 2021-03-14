@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12 col-md">
                <p class="mb-0">
                    <a href="{{ url()->previous(action('MoneyTransferController@index')) }}" class="btn-muted font-weight-bold text-decoration-none">
                        <span class="icon icon-sm">
                            <i class="fas fa-arrow-left"></i>
                        </span>
                        <span>Vissza az előző oldalra</span>
                    </a>
                </p>
                <h1 class="font-weight-bold mb-4">Átutalás rögzítése</h1>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <div class="card card-body text-center">
                    <x-steps-list>
                        <x-steps-list-item :href="action('MoneyTransferController@chooseReseller')" :active="true">
                            Viszonteladó
                        </x-steps-list-item>
                        <x-steps-list-item :href="action('MoneyTransferController@chooseOrders')">Megrendelések
                        </x-steps-list-item>
                        <x-steps-list-item :href="action('MoneyTransferController@create')">Megerősítés
                        </x-steps-list-item>
                    </x-steps-list>

                    <h3 class="mb-4 font-weight-bold">Kinek szeretnél utalni?</h3>

                    <div class="row">
                        <div class="col-10 offset-1">
                            <form action="{{ action('MoneyTransferController@storeReseller') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="mt-reseller-id" class="d-flex align-items-center mb-0">Viszonteladó
                                        *</label>
                                    <select name="mt-reseller-id" id="mt-reseller-id" class="form-control">
                                        @php /** @var \App\User $reseller */ @endphp
                                        @foreach($resellers as $reseller)
                                            <option value="{{ $reseller->id }}">{{ $reseller->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group mt-4 mb-0 text-right">
                                    <button type="submit" class="btn btn-sm btn-success">Tovább a megrendeléseihez</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(() => {
            $('#mt-reseller-id').select2();
        });
    </script>
@endsection

