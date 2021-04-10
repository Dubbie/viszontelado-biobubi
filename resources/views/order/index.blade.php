@extends('layouts.app')
@section('content')
    <div class="container">
        <div id="filter-order">
            <p class="mb-3">
                <small>Szűrés</small>
            </p>
            <form id="form-orders-filter" class="form-row align-items-end">
                <div class="col-xl-3">
                    <div class="form-group">
                        <label for="filter-query">Keresett kifejezés</label>
                        <input type="text" id="filter-query" name="filter-query"
                               class="form-control form-control-sm"
                               value="@if(array_key_exists('query', $filter)) {{ $filter['query'] }} @endif">
                    </div>
                </div>
                <div class="form-group col-xl-3 col-lg-5 col-md-4">
                    <label for="filter-status">Állapot</label>
                    <select name="filter-status" id="filter-status" class="custom-select custom-select-sm">
                        <option value="">Mindegy</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status->name }}"
                                    @if(array_key_exists('status', $filter) && $filter['status'] == $status->name) selected @endif>{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="filter-completed-container" class="form-group col-xl-2 col-lg-5 col-md-4">
                    <label for="filter-delivered">Kiszállítva</label>
                    <select name="filter-delivered" id="filter-delivered" class="custom-select custom-select-sm">
                        <option value="">Mindegy</option>
                        <option
                            value="true"
                            @if(array_key_exists('delivered', $filter) && $filter['delivered'] == true) selected @endif>
                            Igen
                        </option>
                        <option
                            value="false"
                            @if(array_key_exists('delivered', $filter) && $filter['delivered'] == false) selected @endif>
                            Nem
                        </option>
                    </select>
                </div>

                @if(Auth::user()->regions()->count() > 0)
                    <div class="form-group col-xl-3 col-lg-5 col-md-4">
                        <label for="filter-region">Régió</label>
                        <select name="filter-region" id="filter-region" class="custom-select custom-select-sm">
                            <option value="">Összes</option>
                            @foreach(Auth::user()->regions as $region)
                                <option value="{{ $region->id }}"
                                        @if(array_key_exists('region', $filter) && $filter['region'] == $region->id) selected @endif>{{ $region->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if(Auth::user()->admin)
                    <div class="form-group col-xl-3 col-lg-5 col-md-5">
                        <label for="filter-reseller">Viszonteladó</label>
                        <select name="filter-reseller" id="filter-reseller"
                                class="custom-select custom-select-sm">
                            <option value="">Saját megrendeléseim</option>
                            @foreach($resellers as $reseller)
                                <option value="{{ $reseller->id }}"
                                        @if(array_key_exists('reseller', $filter) && $filter['reseller'] == $reseller->id) selected @endif>{{ $reseller->name }}</option>
                            @endforeach
                            <option value="ALL"
                                    @if(array_key_exists('reseller', $filter) && $filter['reseller'] == 'ALL') selected @endif>
                                Összes viszonteladó
                            </option>
                        </select>
                    </div>
                @endif
                <div class="col-xl text-right">
                    <div class="form-group">
                        <button type="submit" class="btn btn-sm btn-success">Szűrés</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="row align-items-baseline">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Megrendelések</h1>
            </div>
            <div class="col text-right">
                <div class="d-flex justify-content-between justify-content-md-end mb-3">
                    @if(Auth::user()->admin)
                        <a href="{{ action('ShoprenterController@updateOrders', ['privateKey' => env('PRIVATE_KEY')]) }}"
                           data-toggle="tooltip"
                           title="Utoljára {{ $lastUpdate['human'] }} frissítve  -  {{ $lastUpdate['datetime']->format('Y. m. d. H:i') }}"
                           data-placement="left"
                           class="btn btn-sm btn-outline-secondary has-tooltip mr-2">Megrendelések frissítése</a>
                    @endif
                    <a href="https://biobubi.hu/" target="_blank" class="btn btn-sm btn-teal">Új rendelés
                        leadása</a>
                </div>
            </div>
        </div>

        @if(Auth::user()->admin && count(Auth::user()->regions) == 0)
            <div class="alert alert-info">
                <p class="mb-0">A fiókodhoz nincs hozzárendelve régió,
                    ezért nem kapsz megrendeléseket.</p>
            </div>
        @endif

        @include('modal.order-comments')
        @if(count($orders) > 0)
            @foreach($orders as $order)
                <x-order :order="$order" type="regular" :worksheet="null"></x-order>
            @endforeach
        @else
            @if(!empty($filter))
                <div class="card card-body">
                    <div class="row align-items-center">
                        <div class="col-12 col-md-3">
                            <img src="{{ url('storage/img/empty.png') }}" class="d-block w-100"
                                 alt="Üres lista ikon">
                        </div>
                        <div class="col">
                            <p class="lead">Az általad beállított szűrők alapján nem találtunk megfelelő
                                megrendeléseket!</p>
                            <a href="https://biobubi.hu/" target="_blank" class="btn btn-sm btn-teal">Új rendelés
                                leadása</a>
                            <a href="{{ action('OrderController@index') }}"
                               class="btn btn-sm btn-outline-secondary">Szűrési
                                feltételek törlése</a>
                        </div>
                    </div>
                </div>
            @else
                <div class="card card-body">
                    <div class="row align-items-center">
                        <div class="col-12 col-md-3">
                            <img src="{{ url('storage/img/empty.png') }}" class="d-block w-100"
                                 alt="Üres lista ikon">
                        </div>
                        <div class="col">
                            <p class="lead">Jelenleg még nincs egy megrendelésed sem.<br>Aggodalomra semmi ok, amint
                                érkezik
                                egy itt fogod látni!</p>
                            <a href="https://biobubi.hu/" target="_blank" class="btn btn-sm btn-teal">Új rendelés
                                leadása</a>
                        </div>
                    </div>
                </div>
            @endif
        @endif

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
                if (selectStatus.options[selectStatus.selectedIndex].value !== '') {
                    console.log(window.location);
                    $(containerCompleted).find('select')[0].disabled = true;
                }

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

        /* modal feltöltő js - megjegyzésekhez */
        //kikukázza a linkeket
        let commentButtons = document.getElementsByName("comments-modal-link");
        //rárakja mindre az eventlistenert
        commentButtons.forEach((comment) => {
            comment.addEventListener('click', fetchModalData);
        });

        //küldi a requestet, megnézi hogy melyik linket nyomta az emberünk
        async function fetchModalData(e) {
            let id = e.currentTarget.getAttribute("data-order-id");
            //feltölti a modal testét
            let modal = document.getElementById("modal-body-content");
            await fetch("/megrendelesek/" + id + "/megjegyzesek/html").then(res => res.text()).then(data => modal.innerHTML = data);
        }
    </script>
@endsection
