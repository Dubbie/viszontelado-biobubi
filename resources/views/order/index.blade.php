@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row align-items-baseline">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Megrendelések</h1>
            </div>
            <div class="col text-right">
                @if(Auth::user()->admin)
                    <a href="{{ action('ShoprenterController@updateOrders', ['privateKey' => env('PRIVATE_KEY')]) }}"
                       data-toggle="tooltip"
                       title="Utoljára {{ $lastUpdate['human'] }} frissítve  -  {{ $lastUpdate['datetime']->format('Y. m. d. H:i') }}"
                       data-placement="left"
                       class="btn btn-sm btn-outline-secondary has-tooltip">Megrendelések frissítése</a>
                @endif
                <a href="https://biobubi.hu/" target="_blank" class="btn btn-sm btn-teal">Új rendelése leadása</a>
            </div>
        </div>

        @if(Auth::user()->admin && count(Auth::user()->zips) == 0)
            <div class="alert alert-info">
                <p class="mb-0">Ez a fiók adminisztrátori jogkörrel rendelkezik és nincs hozzárendelve irányítószám,
                    ezért csak azokat a megrendeléseket látod, amikhez nincs hozzárendelve egy viszonteladó sem.</p>
            </div>
        @endif

        <div id="filter-order">
            <p class="mb-0">
                <small>Szűrés</small>
            </p>
            <form id="form-orders-filter">
                <div class="form-row align-items-end">
                    <div class="col-xl">
                        <div class="form-group">
                            <label for="filter-query">Keresett kifejezés</label>
                            <input type="text" id="filter-query" name="filter-query"
                                   class="form-control form-control-sm"
                                   value="@if(array_key_exists('query', $filter)) {{ $filter['query'] }} @endif">
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-5 col-md-4">
                        <div class="form-group">
                            <label for="filter-status">Állapot</label>
                            <select name="filter-status" id="filter-status" class="custom-select custom-select-sm">
                                <option value="">Mindegy</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->name }}"
                                            @if(array_key_exists('status', $filter) && $filter['status'] == $status->name) selected @endif>{{ $status->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @if(Auth::user()->admin)
                        <div class="col-xl-3 col-lg-5 col-md-5">
                            <div class="form-group">
                                <label for="filter-reseller">Viszonteladó</label>
                                <select name="filter-reseller" id="filter-reseller"
                                        class="custom-select custom-select-sm">
                                    <option value="">Saját megrendeléseim</option>
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
                            <button type="submit" class="btn btn-sm btn-block btn-success">Szűrés</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        @foreach($orders as $order)
            <x-order :order="$order"></x-order>
        @endforeach

        <div class="paginate mt-5">{{ $orders->withQueryString()->links() }}</div>
    </div>

    @include('inc.orders-toolbar')
    @include('modal.mass-order-status')
    @include('modal.mass-update-reseller')
@endsection

@section('scripts')
    {{-- Szűrő --}}
    <script>
        $(function () {
            $('#form-orders-filter').submit(function () {
                var $empty_fields = $(this).find(':input').filter(function () {
                    return $(this).val() === '';
                });
                $empty_fields.prop('disabled', true);
                return true;
            });
        });
    </script>

    {{-- Tömeges státusz változtató --}}
    <script>
        $(() => {
            const chOrders = $('.ch-order-select');
            const ordersCount = document.getElementById('toolbar-order-counter');
            const toolbar = document.getElementById('toolbar-orders');
            const $inputOrderIds = $('.mass-order-id-input');

            /**
             * Visszaállítja a checkboxokat.
             */
            function resetOrderCheckboxes() {
                chOrders.each((i, el) => {
                    el.checked = false;
                });
            }

            /**
             * Visszaadja a kiválasztott megrendelések azonosítóit.
             */
            function getSelectedOrders() {
                let selectedOrders = [];
                chOrders.each((i, el) => {
                    const orderId = el.dataset.orderId;
                    if (el.checked) {
                        selectedOrders.push(orderId);
                    }
                });
                return selectedOrders;
            }

            /**
             * Frissíti a megrendelések toolbarját
             */
            function updateOrdersToolbar() {
                const selectedOrders = getSelectedOrders();
                if (selectedOrders.length > 0) {
                    toolbar.classList.add('show');
                } else {
                    toolbar.classList.remove('show');
                }
                ordersCount.innerText = selectedOrders.length.toLocaleString();
                for (const el of $inputOrderIds) {
                    el.value = JSON.stringify(selectedOrders);
                }
            }

            /**
             * Toolbar frissítő bigyó
             */
            chOrders.on('change', () => {
                updateOrdersToolbar();
            });

            $('.form-complete-order').on('submit', e => {
                if (!confirm('Biztosan teljesíted a megrendelést?')) {
                    e.preventDefault();
                }
            });

            resetOrderCheckboxes();
            $('#mur-reseller-id').select2({
                width: '100%',
            });
        });
    </script>
@endsection
