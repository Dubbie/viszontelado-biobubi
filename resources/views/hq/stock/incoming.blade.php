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
                <h1 class="font-weight-bold mb-4">Központi készlet feltöltése</h1>

                <div class="card card-body p-md-5">
                    <figure class="figure mb-0">
                        <div class="row">
                            <div class="col-4">
                                <img class="figure-img img-fluid rounded" src="{{ $product->picture_url }}" alt="A feltölteni kívánt termék képe...">
                            </div>
                            <div class="col-8">
                                <p class="mb-0 font-weight-bold h5">{{ $product->name }}</p>
                                <p class="text-muted"><small class="font-weight-bold">Cikkszám: {{ $product->sku }}</small></p>
                            </div>
                        </div>

                        <p class="d-flex text-muted mt-3">
                            <small class="p-1 w-100 rounded badge badge-arrow">Készleten: <span id="original-amount" class="text-dark font-weight-bold">{{ $inventoryOnHand }} db</span></small>
                            <small class="p-1 w-100 text-center ml-2">Igazítva: <span id="adjusted-amount" class="text-dark font-weight-bold">0 db</span></small>
                        </p>
                    </figure>

                    <hr class="mb-4">

                    <form action="{{ action('CentralStockController@handleIncoming') }}" method="POST">
                        @csrf
                        <input type="hidden" name="is-sku" value="{{ $product->sku }}">
                        <div class="input">
                            <div class="form-group row">
                                <label for="is-amount" class="col-sm-4 col-form-label">Mennyiség</label>
                                <div class="col-sm-8">
                                    <input id="is-amount" name="is-amount" type="tel" class="form-control" required>
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
            const elAmount = document.getElementById('is-amount');
            const elOriginal = document.getElementById('original-amount');
            const elAdjusted = document.getElementById('adjusted-amount');

            function debounce(fn, duration) {
                var timer;
                return function(){
                    clearTimeout(timer);
                    timer = setTimeout(fn, duration);
                }
            }

            function updateAmount() {
                if (elAmount.value.length === 0) {
                    elAdjusted.innerText = elOriginal.innerText;
                    return;
                }

                const adjustedAmount = parseInt(elOriginal.innerText.replace(' db', '').replaceAll(' ', '')) + parseInt(elAmount.value.replaceAll(' ', ''));
                elAdjusted.innerText = adjustedAmount.toLocaleString().replaceAll(',', ' ') + ' db';
            }

            function bindAllElements() {
                $(elAmount).on('keydown', () => {
                    elAdjusted.innerText = 'Számítás...';
                });

                $(elAmount).on('keyup', debounce(() => {
                    updateAmount();
                }, 250));
            }

            function applyMasks() {
                $(elAmount).mask('000 000 000 000 000', {reverse: true});
            }

            function init() {
                bindAllElements();
                applyMasks();
                updateAmount();
            }

            init();
        });
    </script>
@endsection