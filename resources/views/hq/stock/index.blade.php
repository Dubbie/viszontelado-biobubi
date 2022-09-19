@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Központi készlet</h1>
            </div>
            @if(Auth::user()->admin)
                <div class="col text-right">
                    <a href="{{ action('CentralStockController@history') }}"
                       class="btn btn-muted">Történet</a>
                    <a href="#stockMovement" data-toggle="modal" data-target="#stockMovement"
                       class="btn btn-teal shadow-sm">Készlet mozgatása</a>
                </div>
            @endif
        </div>

        <div class="card card-body p-md-5 mb-4">
            <div id="cs-list">{!! resolve('App\Subesz\StockService2')->getCentralStockHTML() !!}</div>
        </div>
    </div>

    @include('modal.stock.stock-movement')
@endsection

@section('scripts')
    <script>
        $(() => {
            const rsAddRows = document.getElementById('rs-add-rows');
            const rsAddForm = document.getElementById('rs-add-form');
            const rsAddModal = document.getElementById('addStockToReseller');
            const csNewRows = document.getElementById('cs-new-rows');
            const csNewForm = document.getElementById('cs-new-form');
            const csList = document.getElementById('cs-list');
            const csNewModal = document.getElementById('newCentralStock');

            function bindAllElements() {
                // Betöltjük a részleteit a viszonteladónak
                $('.btn-toggle-rs-add-modal').on('click', e => {
                    $(rsAddForm).find('input[name="rs-add-reseller-id"]')[0].value = e.currentTarget.dataset.resellerId;
                    updateRsAddDynamicElements();
                });

                $(document).on('click', '.btn-remove-cs-row', e => {
                    const btn = e.currentTarget;
                    const row = $(btn).closest('.cs-row')[0];
                    $(row).animate({
                        opacity: 0,
                        marginLeft: '100px',
                    }, 350, () => {
                        // Töröljük
                        csNewRows.removeChild(row);
                    });
                });

                $(document).on('click', '.btn-remove-rs-row', e => {
                    const btn = e.currentTarget;
                    const row = $(btn).closest('.rs-row')[0];
                    $(row).animate({
                        opacity: 0,
                        marginLeft: '100px',
                    }, 350, () => {
                        // Töröljük
                        csNewRows.removeChild(row);
                    });
                });

                $(document).on('change', 'select[name="cs-new-product[]"]', e => {
                    updateNewPrices();
                });
                $(document).on('keyup', 'input[name="cs-new-product-qty[]"]', e => {
                    if (e.currentTarget.value.length > 0) {
                        updateNewPrices();
                    }
                });

                $(document).on('change', 'select[name="rs-add-stock[]"], input[name="rs-add-stock-qty[]"]', e => {
                    updateRsAddDynamicElements();
                });
                $(document).on('keyup', 'input[name="rs-add-stock-qty[]"]', e => {
                    if (e.currentTarget.value.length > 0) {
                        updateRsAddDynamicElements();
                    }
                });

                $('#btn-new-cs').on('click', e => {
                    const btn = e.currentTarget;
                    // A gombot inaktívvá tesszük, hogy ne tudja spammolni
                    btn.classList.add('disabled');
                    btn.disabled = true;

                    // Betöltünk egy új sort
                    $.ajax('{{ action('CentralStockController@getCentralStockRow') }}').done(html => {
                        $(csNewRows).append(html);
                        $(csNewRows.lastChild).slideDown(350);
                    }).always(() => {
                        btn.classList.remove('disabled');
                        btn.disabled = false;
                        updateNewPrices();
                    });
                });

                $('#btn-add-rs').on('click', e => {
                    const btn = e.currentTarget;
                    // A gombot inaktívvá tesszük, hogy ne tudja spammolni
                    btn.classList.add('disabled');
                    btn.disabled = true;

                    // Betöltünk egy új sort
                    $.ajax('{{ action('CentralStockController@getResellerStockRow') }}').done(html => {
                        $(rsAddRows).append(html);
                        $(rsAddRows.lastChild).slideDown(350);
                    }).always(() => {
                        btn.classList.remove('disabled');
                        btn.disabled = false;
                        updateRsAddDynamicElements();
                    });
                });

                // Központi készlet hozzáadása
                $(csNewForm).on('submit', e => {
                    e.preventDefault();

                    const btn = $(e.currentTarget).find('button[type="submit"]')[0];
                    btn.classList.add('disabled');
                    btn.disabled = true;

                    // Beküldjük az ajaxot
                    $.ajax('{{ action('CentralStockController@store') }}', {
                        method: 'POST',
                        data: $(csNewForm).serializeArray()
                    }).done(response => {
                        console.log(response);
                        csNewRows.innerHTML = response.csNewHTML;
                        csList.innerHTML = response.csListHTML;
                        $(csNewModal).modal('toggle');
                    }).always(() => {
                        btn.classList.remove('disabled');
                        btn.disabled = false;
                        updateNewPrices();
                    });
                });

                // Viszonteladó készletének frissítése
                $(rsAddForm).on('submit', e => {
                    const btn = $(e.currentTarget).find('button[type="submit"]')[0];
                    btn.classList.add('disabled');
                    btn.disabled = true;
                });
            }

            function updateNewPrices() {
                // Frissítjük a központi készletet
                for (const el of $('#newCentralStock').find('.cs-row')) {
                    const grossPrice = $(el).find('select[name="cs-new-product[]"] option:selected')[0].dataset.grossPrice;
                    const qty = $(el).find('input[name="cs-new-product-qty[]"]')[0].value;
                    $(el).find('.cs-gross-price')[0].innerText = grossPrice.toLocaleString()  + ' Ft';
                    $(el).find('.cs-total-price')[0].innerText = (grossPrice * qty).toLocaleString()  + ' Ft';
                }
            }

            function updateRsAddDynamicElements() {
                // Frissítjük a központi készletet
                for (const el of $(rsAddForm).find('.rs-row')) {
                    const grossPrice = $(el).find('select[name="rs-add-stock[]"] option:selected')[0].dataset.grossPrice;
                    const qty = $(el).find('input[name="rs-add-stock-qty[]"]')[0].value;
                    $(el).find('.rs-gross-price')[0].innerText = grossPrice.toLocaleString()  + ' Ft';
                    $(el).find('.rs-total-price')[0].innerText = (grossPrice * qty).toLocaleString()  + ' Ft';
                }
            }

            function init() {
                bindAllElements();
                updateNewPrices();
            }

            // init();
        });
    </script>
@endsection