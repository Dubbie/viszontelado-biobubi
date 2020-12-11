@php
    /** @var \App\CentralStock $cs */
@endphp
@if(count($centralStock) > 0)
    <div class="row d-none d-md-flex">
        <div class="col-md-8">
            <p class="mb-0"><small class="font-weight-bold">Termék</small></p>
        </div>
        <div class="col-md-2 text-right">
            <p class="mb-0"><small class="font-weight-bold">Darabszám</small></p>
        </div>
        <div class="col-md-2 text-right">
            <p class="mb-0"><small class="font-weight-bold">Érték (Nagyker. Ár)</small></p>
        </div>
    </div>
    @foreach($centralStock as $cs)
        <div class="row align-items-center mb-3 mb-md-2">
            <div class="col-md-8">
                <p class="h5 font-weight-bold mb-0">{{ $cs->product->name }}</p>
                <p class="text-muted mb-0">Cikkszám: <b>{{ $cs->product->sku }}</b></p>
            </div>
            <div class="col-6 col-md-2 text-left text-md-right">
                <p class="mb-0">{{ $cs->inventory_on_hand }} db</p>
            </div>
            <div class="col-6 col-md-2 text-right">
                <p class="lead text-muted mb-0">{{ number_format(($cs->inventory_on_hand * $cs->product->wholesale_price), 0, '.', ' ') . ' Ft' }}</p>
            </div>
        </div>
    @endforeach
    <hr>
    <p class="text-right mb-0 mt-4">
        <small>Készlet összértéke: </small>
        <b class="h4">{{ resolve('App\Subesz\StockService')->getCentralStockValue(true) }}</b>
    </p>
@else
    <p class="lead text-muted">A központi készlet még nem lett feltöltve.</p>
    <p class="mb-0">
        <a href="#newCentralStock" data-toggle="modal" data-target="#newCentralStock"
           class="btn btn-sm btn-teal shadow-sm">Készlet hozzáadása</a>
    </p>
@endif