<!-- Modal -->
<div class="modal fade" id="editProduct" tabindex="-1" role="dialog" aria-labelledby="editProductLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ action('TrialProductController@editProduct') }}" method="POST">
                <input type="hidden" id="ep-hidden-product-sku" name="ep-product-sku" value="">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="editProductLabel">Termék szerkesztése</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="media align-items-center">
                        <img id="ep-product-img" src="" alt="" class="d-block bg-muted mr-2 rounded-lg" height="64">
                        <div class="media-body">
                            <p class="h5 font-weight-bold">
                                <span id="ep-product-name" class="d-block">Termék megnevezése</span>
                                <small class="text-muted">Cikkszám: <span id="ep-product-sku" class="font-weight-bold">SKU</span></small>
                            </p>
                        </div>
                    </div>
                    <div class="form-row mt-4">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="ep-gross-price">Bruttó ár</label>
                                <input type="text" name="ep-gross-price" id="ep-gross-price" class="form-control disabled" disabled="disabled">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="ep-purchase-price">Beszerzési ár</label>
                                <div class="input-group">
                                    <input type="number" name="ep-purchase-price" id="ep-purchase-price" class="form-control" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">Ft</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="ep-wholesale-price">Nagyker Bruttó Ár</label>
                                <div class="input-group">
                                    <input type="number" name="ep-wholesale-price" id="ep-wholesale-price" class="form-control" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">Ft</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p class="text-muted mb-0"><small>A bruttó árat a Shoprenter-be tudod változatni.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-link" data-dismiss="modal">Vissza</button>
                    <button type="submit" class="btn btn-sm btn-success">Termék frissítése</button>
                </div>
            </form>
        </div>
    </div>
</div>