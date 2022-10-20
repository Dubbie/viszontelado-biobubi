<div id="toolbar-transfers" class="toolbar">
    <div class="container py-2">
        <div class="row no-gutters align-items-center">
            <div class="col-lg-3">
                <p class="h5 mb-2 mb-md-0">
                    <span id="toolbar-order-counter" class="badge badge-success mr-2">0</span> átutalás kijelölve
                </p>
            </div>
            <div class="col-lg-auto">
                <form action="{{ action('MoneyTransferController@generateExcel') }}" method="POST">
                    @csrf
                    <input type="hidden" id="dl-transfer-ids" name="dl-transfer-ids" class="mass-transfer-id-input" value="">
                    <button type="submit" class="btn btn-muted has-tooltip" data-toggle="tooltip"
                            title="Táblázat letöltése">
                        <span class="icon text-dark">
                            <i class="fas fa-file-excel"></i>
                        </span>
                        <span class="d-inline-block">Excel táblázat letöltése</span>
                    </button>
                </form>
            </div>
            <div class="col-lg-auto">
                <form action="{{ action('MoneyTransferController@multiDestroy') }}" method="POST">
                    @csrf
                    <input type="hidden" id="destroy-transfer-ids" name="destroy-transfer-ids" class="mass-transfer-id-input" value="">
                    <button type="submit" class="btn btn-muted has-tooltip" data-toggle="tooltip"
                            title="Átutalások törlése">
                        <span class="icon text-dark">
                            <i class="fas fa-trash"></i>
                        </span>
                        <span class="d-inline-block">Átutalások törlése</span>
                    </button>
                </form>
            </div>

            <div class="col-lg-auto">
                <form action="{{ action('MoneyTransferController@multiGenerateCommissions') }}" method="POST">
                    @csrf
                    <input type="hidden" id="com-transfer-ids" name="com-transfer-ids" class="mass-transfer-id-input" value="">
                    <button type="submit" class="btn btn-muted has-tooltip" data-toggle="tooltip"
                            title="Jutalék számlák generálása">
                        <span class="icon text-dark">
                            <i class="fa-solid fa-sack-dollar"></i>
                        </span>
                        <span class="d-inline-block">Jutalék számlák generálása</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>