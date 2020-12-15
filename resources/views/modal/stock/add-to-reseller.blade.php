<!-- Modal -->
<div class="modal fade" id="addStockToReseller" tabindex="-1" role="dialog" aria-labelledby="addStockToResellerLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="rs-add-form" action="{{ action('CentralStockController@addStockToReseller') }}" method="POST">
                @csrf
                <input type="hidden" name="rs-add-reseller-id">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStockToResellerLabel">Viszonteladó készlet feltöltése</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- Sorok ahol tudja a készletet módosítani --}}
                    <div id="rs-add-rows">{!! resolve('App\Subesz\StockService')->getResellerStockRow(true) !!}</div>

                    {{-- Új készlet bejegyzés gombi gomb --}}
                    <button type="button" id="btn-add-rs"
                            class="btn btn-link p-0 text-decoration-none mb-0">+ Készlet hozzáadása
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-link" data-dismiss="modal">Vissza</button>
                    <button type="submit" class="btn btn-sm btn-success">Készlet mentése</button>
                </div>
            </form>
        </div>
    </div>
</div>