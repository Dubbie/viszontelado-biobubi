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

        <div class="row">
            <div class="col">
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
                                                <option value="{{ $item->sku }}">{{ $item->productDescriptions[0]->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3">
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

            <div id="user-column" class="col-4" style="display: none;">
                <div class="card card-body">
                    <p id="user-stock-title" class="text-small font-weight-bold mb-4">Viszonteladó készlete</p>

                    <ul id="user-stock-list" class="list-unstyled mb-0"></ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(() => {
            const stockContainer = document.getElementById('stock-rows-container');
            const userColumn = document.getElementById('user-column');
            const userStockList = document.getElementById('user-stock-list');
            const userChooser = document.getElementById('stock-user-id');
            const userStockTitle = document.getElementById('user-stock-title');

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

                $(userChooser).on('change', e => {
                    loadUserStockView(e.currentTarget.value);
                });
            }

            function loadUserStockView(userId) {
                $.ajax('{{ url('') }}/kozponti-keszlet/' + userId + '/lekerdezes')
                    .then(data => {
                        updateUserStockList(data);
                    });
            }

            function updateUserStockList(stockList) {
                userStockTitle.innerText = 'Készlet információ';
                $(userColumn).fadeOut();
                while (userStockList.lastChild) {
                    userStockList.removeChild(userStockList.lastChild);
                }

                for (const stockEntry of stockList) {
                    const li = createStockListItem(stockEntry);
                    if (stockList[stockList.length - 1] !==  stockEntry) {
                        li.classList.add('mb-2');
                    }
                    userStockList.appendChild(li);
                }

                if (userStockList.children.length > 0) {
                    userStockTitle.innerText = `${stockList[0]['reseller']['name']} készlete`;
                    $(userColumn).fadeIn();
                }
            }

            function createStockListItem(stockEntry) {
                const li = document.createElement('li');
                const row = document.createElement('div');
                row.classList.add('row');

                const colLeft = document.createElement('div');
                colLeft.classList.add('col-md-8');
                const prodName = document.createElement('span');
                prodName.classList.add('font-weight-bold', 'mr-2');
                prodName.innerText = `${stockEntry['product']['name']}`;
                const sku = document.createElement('span');
                sku.classList.add('text-muted');
                sku.innerText = `(${stockEntry['sku']})`;
                colLeft.appendChild(prodName);
                colLeft.appendChild(sku);

                const colRight = document.createElement('div');
                colRight.classList.add('col-md-4', 'text-right');
                colRight.innerText = `${stockEntry['inventory_on_hand']} db`;

                row.appendChild(colLeft);
                row.appendChild(colRight);
                li.appendChild(row);

                return li;
            }

            function refreshCountMask() {
                $('.input-count').mask('000 000 000 000 000', {reverse: true});
            }

            function init() {
                $('.custom-select').select2({width: '100%'});
                refreshCountMask();
                bindAllElements();

                loadUserStockView(userChooser.options[userChooser.selectedIndex].value);
            }

            init();
        });
    </script>
@endsection