<div id="toolbar-orders" class="toolbar">
    <div class="container py-2">
        <div class="row align-items-center">
            <div class="col-lg-3">
                <p class="h5 mb-0">
                    <span id="toolbar-order-counter" class="badge badge-success mr-2">0</span> megrendelés kijelölve
                </p>
            </div>
            <div class="col-lg-auto">
                <p class="mb-0">
                    <button type="button" class="btn btn-muted" data-toggle="modal"
                        data-target="#massOrderStatusModal">
                        <span class="icon text-dark">
                            <i class="fas fa-pen"></i>
                        </span>
                        <span>Tömeges állapot változtatás</span>
                    </button>
                </p>
            </div>
            <div class="col-lg">
                <form action="{{ action('DocumentController@download') }}" method="POST">
                    @csrf
                    <input type="hidden" id="sm-order-ids" name="sm-order-ids" value="">
                    <p class="mb-0">
                        <button type="submit" class="btn btn-muted">
                        <span class="icon text-dark">
                            <i class="fas fa-envelope"></i>
                        </span>
                            <span>Szállítólevél letöltése</span>
                        </button>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>