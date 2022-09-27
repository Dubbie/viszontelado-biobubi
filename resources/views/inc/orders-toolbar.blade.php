<div id="toolbar-orders" class="toolbar">
    <div class="container py-2">
        <div class="row no-gutters align-items-center">
            <div class="col-lg-3">
                <p class="h5 mb-2 mb-md-0">
                    <span id="toolbar-order-counter" class="badge badge-success mr-2">0</span> megrendelés kijelölve
                </p>
            </div>
            <div class="col-lg-auto">
                <p class="mb-0 has-tooltip" data-toggle="tooltip" title="Tömeges állapot változtatás">
                    <button type="button" class="btn btn-muted" data-toggle="modal"
                            data-target="#massOrderStatusModal">
                        <span class="icon text-dark">
                            <i class="fas fa-pen"></i>
                        </span>
                        <span class="d-inline-block d-md-none">Tömeges állapot változtatás</span>
                    </button>
                </p>
            </div>
            @if(Auth::user()->admin)
                <div class="col-lg-auto">
                    <p class="mb-0 has-tooltip" data-toggle="tooltip" title="Tömeges viszonteladó váltás">
                        <button type="button" class="btn btn-muted" data-toggle="modal"
                                data-target="#massUpdateResellerModal">
                            <span class="icon text-dark">
                                <i class="fas fa-exchange-alt"></i>
                            </span>
                            <span class="d-inline-block d-md-none">Tömeges viszonteladó váltás</span>
                        </button>
                    </p>
                </div>
            @endif
            <div class="col-lg-auto">
                <form action="{{ action('WorksheetController@addMultiple') }}" method="POST">
                    @csrf
                    <input type="hidden" id="mws-order-ids" name="mws-order-ids" class="mass-order-id-input" value="">
                    <button type="submit" class="btn btn-muted has-tooltip" data-toggle="tooltip"
                            title="Tömeges munkalapra küldés">
                        <span class="icon text-dark">
                            <i class="fas fa-file-import"></i>
                        </span>
                        <span class="d-inline-block d-md-none">Tömeges munkalapra küldés</span>
                    </button>
                </form>
            </div>
            <div class="col-lg-auto">
                <form action="{{ action('DocumentController@download') }}" method="POST">
                    @csrf
                    <input type="hidden" id="sm-order-ids" name="sm-order-ids" class="mass-order-id-input" value="">
                    <p class="mb-0">
                        <button type="submit" class="btn btn-muted has-tooltip" data-toggle="tooltip"
                                title="Szállítólevél letöltése">
                            <span class="icon text-dark">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <span class="d-inline-block d-md-none">Szállítólevél letöltése</span>
                        </button>
                    </p>
                </form>
            </div>
            @if(Auth::user()->admin)
                <div class="col-lg-auto">
                    <form action="{{ action('OrderController@massRegenerateInvoices') }}" method="POST">
                        @csrf
                        <input type="hidden" id="mri-order-ids" name="mri-order-ids" class="mass-order-id-input" value="">
                        <p class="mb-0">
                            <button type="submit" class="btn btn-muted has-tooltip" data-toggle="tooltip"
                                    title="Számlák újragenerálása és küldése">
                            <span class="icon text-dark">
                                <i class="fas fa-redo"></i>
                            </span>
                                <span class="d-inline-block d-md-none">Számlák újragenerálása és küldése</span>
                            </button>
                        </p>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>