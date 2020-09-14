@extends('layouts.app')

@section('content')
    <div class="container">
        <p class="mb-0">
            <a href="{{ action('StockController@index') }}" class="btn-muted font-weight-bold text-decoration-none">
                <span class="icon icon-sm">
                    <i class="fas fa-arrow-left"></i>
                </span>
                <span>Vissza a készlethez</span>
            </a>
        </p>
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Készlet hozzáadása</h1>
            </div>
        </div>

        <div class="card card-body">
            <form action="{{ action('StockController@store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="stock-user-id">Viszonteladó *</label>
                    <select name="stock-user-id" id="stock-user-id" class="custom-select" required  >
                        <option value="" selected disabled hidden>Kérlek válassz...</option>
                        @php /** @var \App\User $user */ @endphp
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="stock-rows-container">
                    <div class="stock-row form-row align-items-end mb-3" id="stock-row-{{ $hash }}">
                        <div class="col">
                            <div class="form-group mb-0">
                                <label for="stock-item-sku[{{ $hash }}]">Termék *</label>
                                <select name="stock-item-sku[{{ $hash }}]" id="stock-item-sku[{{ $hash }}]" class="custom-select" required>
                                    @foreach($items as $item)
                                        <option value="{{ $item->sku }}|{{ $item->productDescriptions[0]->name }}">{{ $item->productDescriptions[0]->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="from-group mb-0">
                                <label for="stock-item-count[{{ $hash }}]">Darabszám *</label>
                                <div class="input-group">
                                    <input type="tel" id="stock-item-count[{{ $hash }}]" name="stock-item-count[{{ $hash }}]" class="input-count form-control text-right" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">db</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto text-right" style="width: 60px">
                            <button class="btn btn-del mb-1 ml-auto btn-remove-stock has-tooltip"
                                    data-target-id="{{ $hash }}" type="button">
                                <svg width="32px" height="32px" viewBox="0 0 16 16" class="bi bi-x" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Új készlet bejegyzés gombi gomb --}}
                <button type="button" id="btn-new-stock"
                        class="btn btn-link p-0 text-decoration-none mb-4">+ Készlet hozzáadása
                </button>

                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-sm btn-success">Készlet mentése</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(() => {
            const stockContainer = document.getElementById('stock-rows-container');

            /**
             * Eltünteti a megadott azonosítójú sort
             */
            function removeStockRow(targetId) {
                const $target = $('#stock-row-' + targetId);
                if ($target) {
                    // Eltüntetjük
                    $target.animate({
                        opacity: 0,
                        marginLeft: '100px',
                    }, 350, () => {
                        // Töröljük
                        stockContainer.removeChild($target[0]);
                    });
                } else {
                    console.error('Nem található ilyen azonosítójú készlet sor: ' + targetId);
                }
            }

            function bindAllElements() {
                $('#btn-new-stock').on('click', e => {
                    const btn = e.currentTarget;
                    // A gombot inaktívvá tesszük, hogy ne tudja spammolni
                    btn.classList.add('disabled');
                    btn.disabled = true;

                    // Betöltünk egy új sort
                    $.ajax('{{ action('StockController@createRow') }}').done(html => {
                        $(stockContainer).append(html);
                        $(stockContainer.lastChild).slideDown(350);
                    }).always(() => {
                        btn.classList.remove('disabled');
                        btn.disabled = false;
                        refreshCountMask();
                    });
                });

                $(document).on('click', '.btn-remove-stock', e => {
                    // Megkeressük a helyes sort
                    const targetId = e.currentTarget.dataset.targetId;
                    removeStockRow(targetId);
                });
            }

            function refreshCountMask() {
                $('.input-count').mask('000 000 000 000 000', {reverse: true});
            }

            function init() {
                $('.custom-select').select2({width: '100%'});
                refreshCountMask();
                bindAllElements();
            }
            init();
        });
    </script>
@endsection