<!-- Modal -->
<div class="modal fade" id="newCentralStock" tabindex="-1" role="dialog" aria-labelledby="newCentralStockLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="cs-new-form" action="{{ action('CentralStockController@store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="newCentralStockLabel">Központi raktárkészlet feltöltése</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="cs-new-rows">{!! resolve('App\Subesz\StockService')->getCentralStockRow(true) !!}</div>

                    {{-- Új készlet bejegyzés gombi gomb --}}
                    <button type="button" id="btn-new-cs"
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