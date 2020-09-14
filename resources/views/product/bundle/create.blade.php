@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Új csomag létrehozása</h1>
            </div>
        </div>
        <div class="card card-body">
            <form action="{{ action('BundleController@store') }}" method="post">
                @csrf
                <div class="form-group">
                    <label for="bundle-sku">Csomag termék * <small class="text-muted">(A termék a <b>Shoprenter</b>-ből, amelyik több termékből áll)</small></label>
                    <select name="bundle-sku" id="bundle-sku" class="custom-select" required>
                        @foreach($products as $product)
                            <option value="{{ $product->sku }}">{{ $product->name }} ({{ $product->sku }})</option>
                        @endforeach
                    </select>
                </div>

                <hr class="my-4">

                <div id="bundle-product-rows-container">
                    <div class="bundle-product-row form-row align-items-end mb-3" id="bundle-product-row-{{ $hash }}">
                        <div class="col">
                            <div class="form-group mb-0">
                                <label for="bundle-product-sku[{{ $hash }}]">Rész termék *</label>
                                <select name="bundle-product-sku[{{ $hash }}]" id="bundle-product-sku[{{ $hash }}]" class="custom-select" required>
                                    @foreach($products as $product)
                                        <option value="{{ $product->sku }}">{{ $product->name }} ({{ $product->sku }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="from-group mb-0">
                                <label for="bundle-product-count[{{ $hash }}]" class="w-100 text-right">Darabszám *</label>
                                <div class="input-group">
                                    <input type="tel" id="bundle-product-count[{{ $hash }}]" name="bundle-product-count[{{ $hash }}]" class="input-count form-control text-right" value="{{ old('bundle-product[' . $hash . ']', 1) }}" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">db</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-right" style="width: 60px">
                            <button class="btn btn-del mb-1 ml-auto btn-remove-bundle-product has-tooltip d-none"
                                    data-target-id="{{ $hash }}" type="button">
                                <svg width="32px" height="32px" viewBox="0 0 16 16" class="bi bi-x" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Új készlet bejegyzés gombi gomb --}}
                <button type="button" id="btn-new-bundle-product"
                        class="btn btn-link p-0 text-decoration-none mb-4">+ Termék hozzáadása
                </button>

                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-success">Csomag elmentése</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(() => {
            const bundleProductsContainer = document.getElementById('bundle-product-rows-container');

            /**
             * Eltünteti a megadott azonosítójú sort
             */
            function removeBundleProductRow(targetId) {
                const $target = $('#bundle-product-row-' + targetId);
                if ($target) {
                    // Eltüntetjük
                    $target.animate({
                        opacity: 0,
                        marginLeft: '100px',
                    }, 350, () => {
                        // Töröljük
                        bundleProductsContainer.removeChild($target[0]);
                    });
                } else {
                    console.error('Nem található ilyen azonosítójú termék sor: ' + targetId);
                }
            }

            function bindAllElements() {
                $('#btn-new-bundle-product').on('click', e => {
                    const btn = e.currentTarget;
                    // A gombot inaktívvá tesszük, hogy ne tudja spammolni
                    btn.classList.add('disabled');
                    btn.disabled = true;

                    // Betöltünk egy új sort
                    $.ajax('{{ action('BundleController@row') }}').done(html => {
                        $(bundleProductsContainer).append(html);
                        $(bundleProductsContainer.lastChild).slideDown(350);
                    }).always(() => {
                        btn.classList.remove('disabled');
                        btn.disabled = false;
                        refreshQoL();
                    });
                });

                $(document).on('click', '.btn-remove-bundle-product', e => {
                    // Megkeressük a helyes sort
                    const targetId = e.currentTarget.dataset.targetId;
                    removeBundleProductRow(targetId);
                });
            }

            function refreshQoL() {
                $('.input-count').mask('000 000 000 000 000', {reverse: true});
                $('.custom-select').select2({width: '100%'});
            }

            function init() {
                refreshQoL();
                bindAllElements();
            }
            init();
        });
    </script>
@endsection
