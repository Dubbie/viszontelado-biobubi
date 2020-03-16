<!-- Modal -->
<div class="modal fade" id="orderStatusModal" tabindex="-1" role="dialog" aria-labelledby="orderStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ action('OrderController@updateStatus') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="orderStatusModalLabel">Megrendelés állapotának módosítása</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="modal-loader">
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <div class="text ml-3">
                                <p class="font-weight-bold mb-0">Megrendelés részleteinek lekérése...</p>
                                <p class="text-muted mb-0"><small>Már csak másodpercek vannak hátra!</small></p>
                            </div>
                        </div>
                    </div>
                    <div id="order-status-details" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-link" data-dismiss="modal">Vissza</button>
                    <button type="submit" class="btn btn-sm btn-success">Állapot frissítése</button>
                </div>
            </form>
        </div>
    </div>
</div>