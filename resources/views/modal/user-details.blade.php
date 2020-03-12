<!-- Modal -->
<div class="modal fade" id="userDetailsModal" tabindex="-1" role="dialog" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userDetailsModalLabel">Felhasználó részletek</h5>
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
                            <p class="font-weight-bold mb-0">Felhasználó részleteinek lekérése...</p>
                            <p class="text-muted mb-0"><small>Már mindjárt betölt, esküszöm!</small></p>
                        </div>
                    </div>
                </div>
                <div id="user-details" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-link" data-dismiss="modal">Vissza</button>
            </div>
        </div>
    </div>
</div>