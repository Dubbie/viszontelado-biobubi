@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Megrendelések</h1>
            </div>
            @if(Auth()->user()->admin)
                <div class="col text-right">
                    <a href="{{ action('ShoprenterController@updateOrders', ['privateKey' => env('PRIVATE_KEY')]) }}"
                       data-toggle="tooltip"
                       title="Utoljára {{ $lastUpdate['human'] }} frissítve  -  {{ $lastUpdate['datetime']->format('Y. m. d. H:i') }}"
                       data-placement="left"
                       class="btn btn-sm btn-outline-secondary has-tooltip">Megrendelések frissítése</a>
                </div>
            @endif
        </div>

        @if(Auth()->user()->admin && count(Auth()->user()->zips) == 0)
            <div class="alert alert-info">
                <p class="mb-0">Ez a fiók adminisztrátori jogkörrel rendelkezik és nincs hozzárendelve irányítószám,
                    ezért az összes megrendelést látja.</p>
            </div>
        @endif

        <div id="filter-order">
            <p class="mb-0">
                <small>Szűrés</small>
            </p>
            <form>
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
                    @if(Auth()->user()->admin)
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

        <div class="card card-body">
            <table class="table table-responsive-lg table-sm table-borderless mb-0">
                <thead>
                <tr>
                    <th>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input ch-order-select"
                                   name="ch-order-select-all"
                                   id="ch-order-select-all">
                            <label class="custom-control-label"
                                   for="ch-order-select-all"></label>
                        </div>
                    </th>
                    <th scope="col">
                        <small class="font-weight-bold">Ügyfél</small>
                    </th>
                    <th scope="col">
                        <small class="font-weight-bold">Állapot</small>
                    </th>
                    <th scope="col">
                        <small class="font-weight-bold">Város</small>
                    </th>
                    <th scope="col">
                        <small class="font-weight-bold">Kezdő dátum</small>
                    </th>
                    <th scope="col" class="text-right">
                        <small class="font-weight-bold">Összesen</small>
                    </th>
                    <td></td>
                </tr>
                </thead>
                <tbody>
                @foreach($orders as $order)
                    <tr>
                        <td class="align-middle">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input ch-order-select"
                                       name="ch-order-select[]"
                                       id="ch-order-select-{{ $order->inner_id }}"
                                       data-order-id="{{ $order->inner_resource_id }}">
                                <label class="custom-control-label"
                                       for="ch-order-select-{{ $order->inner_id }}"></label>
                            </div>
                        </td>
                        <td>
                            <p class="mb-0">{{ $order->firstname }} {{ $order->lastname }}
                                <small class="d-block text-muted">{{ $order->email }}</small>
                            </p>
                        </td>
                        <td class="align-middle">
                            <p class="mb-0" style="color: {{ $order->status_color }}">{{ $order->status_text }}</p>
                        </td>
                        <td class="align-middle"><p class="mb-0">{{ $order->getFormattedAddress() }}</p></td>
                        <td class="align-middle"><p
                                    class="mb-0 text-nowrap">{{ $order->created_at->format('Y. m. d. H:i') }}</p>
                        </td>
                        <td class="text-right align-middle">
                            <p class="mb-0 text-nowrap">{{ number_format($order->total_gross, 0, '.', ' ') }} Ft</p>
                        </td>
                        <td class="align-middle text-right">
                            <a href="{{ action('OrderController@show', ['orderId' => $order->inner_resource_id]) }}"
                               class="btn-icon">
                                <span class="icon">
                                    <i class="fas fa-expand"></i>
                                </span>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @include('inc.orders-toolbar')
    @include('modal.mass-order-status')
@endsection

@section('scripts')
    {{-- Szűrő --}}
    <script>
        $(function () {
            $('form').submit(function () {
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
            const btnOrderMassStatusUpdate = document.getElementById('btn-order-mass-status-update');
            const chAllOrders = document.getElementById('ch-order-select-all');
            const chOrders = $('.ch-order-select');
            const ordersCount = document.getElementById('toolbar-order-counter');
            const toolbar = document.getElementById('toolbar-orders');
            const inputOrderIds = document.getElementById('order-ids');

            /**
             * Visszaállítja a checkboxokat.
             */
            function resetVehicleCheckboxes() {
                chAllOrders.checked = false;
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
                chAllOrders.checked = chOrders.length === selectedOrders.length;
                if (selectedOrders.length > 0) {
                    toolbar.classList.add('show');
                } else {
                    toolbar.classList.remove('show');
                }
                ordersCount.innerText = selectedOrders.length.toLocaleString();
                inputOrderIds.value = JSON.stringify(selectedOrders);
            }

            /**
             * Megrendelések tömeges státuszváltoztatása gomb
             */
            $(btnOrderMassStatusUpdate).on('click', () => {
                const input = prompt('Biztosan törölni szeretné a kijelölt járműveket?\nEz a folyamat nem visszafordítható!\n\nHa biztos benne írja be azt hogy "TÖRLÉS". (Idézőjelek nélkül)');
                if (input === null || input.toLowerCase() !== 'törlés') {
                    return;
                }
                const toDelete = getSelectedOrders();

                // fetch('/jarmu/torles/tomeges', {
                //     method: 'DELETE',
                //     headers: {
                //         'Content-Type': 'application/json',
                //         'X-CSRF-Token': csrfToken,
                //     },
                //     body: JSON.stringify({
                //         'vehicles': toDelete,
                //     }),
                // }).then(response => response.json()).then(json => {
                //     if (json.success) {
                //         location.reload();
                //     }
                // });
            });

            /**
             * Összes jármű kiválasztó gomb
             */
            $(chAllOrders).on('change', () => {
                let check = chAllOrders.checked;
                chOrders.each((i, el) => {
                    el.checked = check;
                });
                updateOrdersToolbar();
            });

            /**
             * Toolbar frissítő bigyó
             */
            chOrders.on('change', () => {
                updateOrdersToolbar();
            });

            resetVehicleCheckboxes();
        });
    </script>
@endsection