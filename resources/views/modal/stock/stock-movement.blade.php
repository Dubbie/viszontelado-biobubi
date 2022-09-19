<!-- Modal -->
<div class="modal fade" id="stockMovement" tabindex="-1" role="dialog" aria-labelledby="stockMovementLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="cs-new-form" action="{{ action('CentralStockController@store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="stockMovementLabel">Készlet mozgatása</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        @foreach($products as $product)
                            <div class="col-12 col-md-6 col-lg-3">
                                <a href="{{ action('CentralStockController@scanResult', $product->sku) }}" class="font-weight-bold text-dark">
                                    <img src="{{ $product->picture_url }}" alt="" class="img-fluid rounded">
                                    <p class="mb-0">{{ $product->name }}</p>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-link" data-dismiss="modal">Vissza</button>
                    <button type="submit" class="btn btn-sm btn-success">Készlet mentése</button>
                </div>
            </form>
        </div>
    </div>
</div>