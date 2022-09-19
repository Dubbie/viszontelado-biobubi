@extends('layouts.app')

@section('content')
    @php /** @var \App\Product $product */ @endphp
    <div class="container">
        <div class="row">
            <div class="col-lg-6 offset-lg-3">
                <p class="mb-0">
                    <a href="{{ route('hq.stock.scanned', $product->sku) }}" class="btn-muted font-weight-bold text-decoration-none">
                        <span class="icon icon-sm">
                            <i class="fas fa-arrow-left"></i>
                        </span>
                        <span>Vissza a termékhez</span>
                    </a>
                </p>
                <h1 class="font-weight-bold mb-4">Viszonteladónak adás</h1>

                <div class="card card-body p-md-5">
                    <figure class="figure mb-0">
                        <div class="row align-items-center">
                            <div class="col-4">
                                <img class="figure-img img-fluid rounded" src="{{ $product->picture_url }}" alt="A feltölteni kívánt termék képe...">
                            </div>
                            <div class="col-8">
                                <p class="mb-0 font-weight-bold h5">{{ $product->name }}</p>
                                <p class="text-muted"><small class="font-weight-bold">Cikkszám: {{ $product->sku }}</small></p>
                            </div>
                        </div>

                        <p class="mb-0"><small class="font-weight-semibold">Központi készlet:</small></p>
                        <p class="d-flex text-muted">
                            <small class="w-100 rounded badge badge-arrow">Készleten: <span id="original-amount" class="text-dark font-weight-bold">{{ $inventoryOnHand }} db</span></small>
                            <small class="w-100 text-center ml-2">Igazítva: <span id="adjusted-amount" class="text-dark font-weight-bold">0 db</span></small>
                        </p>
                    </figure>

                    <hr class="mb-4">

                    <form action="{{ action('CentralStockController@addStockToReseller') }}" method="POST">
                        @csrf
                        <input type="hidden" name="os-sku" value="{{ $product->sku }}">
                        <div class="input">
                            <div class="form-group row">
                                <label for="os-reseller-id" class="col-sm-4 col-form-label">Viszonteladó</label>
                                <div class="col-sm-8">
                                    <select id="os-reseller-id" name="os-reseller-id" class="form-control" required>
                                        <option value="" disabled>Kérlek válassz...</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>

                                    {{-- Itt mutatjuk meg a viszonteladó készletéhez tartozó adatok --}}
                                    <div id="reseller-stock-container" class="mt-2">
                                        <p class="text-muted">
                                            <small class="p-1 rounded badge badge-arrow">Készleten: <span id="r-original-amount" class="text-dark font-weight-bold">0 db</span></small>
                                            <small class="ml-3">Igazítva: <span id="r-adjusted-amount" class="text-dark font-weight-bold">0 db</span></small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="os-amount" class="col-sm-4 col-form-label">Mennyiség</label>
                                <div class="col-sm-8">
                                    <input id="os-amount" name="os-amount" type="text" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group text-right mb-0">
                            <button type="submit" class="btn btn-success">Mentés</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(() => {
            const elAmount = document.getElementById('os-amount');
            const elOriginal = document.getElementById('original-amount');
            const elAdjusted = document.getElementById('adjusted-amount');
            const elReseller = document.getElementById('os-reseller-id');
            const elOriginalReseller = document.getElementById('r-original-amount');
            const elAdjustedReseller = document.getElementById('r-adjusted-amount');

            function debounce(fn, duration) {
                var timer;
                return function(){
                    clearTimeout(timer);
                    timer = setTimeout(fn, duration);
                }
            }

            function updateAmounts() {
                if (elAmount.value.length === 0) {
                    elAdjusted.innerText = elOriginal.innerText;
                    elAdjustedReseller.innerText = elOriginalReseller.innerText;
                    return;s
                }

                const adjustedAmount = parseInt(elOriginal.innerText.replace(' db', '').replaceAll(' ', '')) - parseInt(elAmount.value.replaceAll(' ', ''));
                const adjustedAmountReseller = parseInt(elOriginalReseller.innerText.replace(' db', '').replaceAll(' ', '')) + parseInt(elAmount.value.replaceAll(' ', ''));
                elAdjusted.innerText = adjustedAmount.toLocaleString().replaceAll(',', ' ') + ' db';
                elAdjustedReseller.innerText = adjustedAmountReseller.toLocaleString().replaceAll(',', ' ') + ' db';
            }

            function fetchResellerStock() {
                elOriginalReseller.innerText = 'Betöltés...';

                const url = '{{ action('StockController@getResellerStockBySKU', ['userId' => -1, 'sku' => 'SKU']) }}';
                const sku = '{{ $product->sku }}';
                const userId = elReseller.options[elReseller.selectedIndex].value;

                $.ajax(url.replace('-1', userId).replace('SKU', sku)).done(amount => {
                    elOriginalReseller.innerText = parseInt(amount).toLocaleString().replaceAll(',', ' ') + ' db';
                });
            }

            function bindAllElements() {
                $(elAmount).on('keydown', () => {
                    elAdjusted.innerText = 'Számítás...';
                });

                $(elAmount).on('keyup', debounce(() => {
                    updateAmounts();
                }, 250));

                $(elReseller).on('change', () => {
                    fetchResellerStock();
                });
            }

            function applyMasks() {
                $(elAmount).mask('000 000 000 000 000', {reverse: true});
            }

            function init() {
                bindAllElements();
                applyMasks();
                updateAmounts();
                fetchResellerStock();
            }

            init();
        });
    </script>
@endsection