@php
    /** @var \App\User $reseller */
@endphp
@if(count($reseller->stock) > 0)
    <div class="row d-none d-md-flex">
        <div class="col-md-8">
            <p class="mb-0"><small class="font-weight-bold">Termék</small></p>
        </div>
        <div class="col-md-2 text-right">
            <p class="mb-0"><small class="font-weight-bold">Darabszám</small></p>
        </div>
        <div class="col-md-2 text-right">
            <p class="mb-0"><small class="font-weight-bold">Érték</small></p>
        </div>
    </div>
    @foreach($reseller->stock as $stock)
        <div class="row align-items-center mb-3 mb-md-2">
            <div class="col-md-8">
                <p class="h5 font-weight-bold mb-0">{{ $stock->product->name }}</p>
                <p class="text-muted mb-0">Cikkszám: <b>{{ $stock->product->sku }}</b></p>
            </div>
            <div class="col-6 col-md-2 text-left text-md-right">
                <p class="mb-0">{{ $stock->inventory_on_hand }} db</p>
            </div>
            <div class="col-6 col-md-2 text-right">
                <p class="lead text-muted mb-0">{{ number_format(($stock->inventory_on_hand * $stock->product->gross_price), 0, '.', ' ') . ' Ft' }}</p>
            </div>
        </div>
    @endforeach
    <hr>
    <p class="text-right mb-0 mt-4">
        <small>Készlet összértéke: </small>
        <b class="h4">{{ resolve('App\Subesz\StockService')->getCentralStockValue(true) }}</b>
    </p>
@else
    <p class="lead text-muted">A viszonteladó készlete még nem lett feltöltve.</p>
@endif