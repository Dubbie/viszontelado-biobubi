@php
    /** @var \App\Stock $stockEntry */
@endphp
@if(count($stock) > 0)
    <div class="row d-none d-md-flex">
        <div class="col-md-7">
            <p class="mb-0"><small class="font-weight-bold">Termék</small></p>
        </div>
        <div class="col-lg-2 text-right">
            <p class="mb-0"><small class="font-weight-bold">Darabszám</small></p>
        </div>
        <div class="col-lg-3 text-right">
            <p class="mb-0"><small class="font-weight-bold">Érték (Nagyker. Ár)</small></p>
        </div>
    </div>
    @foreach($stock as $stockEntry)
        <div class="row align-items-center mb-3 mb-md-2">
            <div class="col-lg-7">
                <div class="row no-gutters align-items-center">
                    <div class="col-2 col-lg-1">
                        <img src="{{ $stockEntry->product->picture_url }}" class="rounded" alt="{{ $stockEntry->product->name }} termékfotója" style="max-width: 48px">
                    </div>
                    <div class="col-10 col-lg-9 pl-3">
                        <p class="h5 font-weight-bold mb-0">{{ $stockEntry->product->name }}</p>
                        <p class="text-muted mb-0">Cikkszám: <b>{{ $stockEntry->product->sku }}</b></p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-2 text-left text-md-right">
                <div class="row no-gutters">
                    <div class="col offset-2">
                        <p class="mb-0 pl-3">{{ number_format($stockEntry->inventory_on_hand, 0, '', ' ') }} db</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-3 text-left text-md-right">
                <div class="row no-gutters">
                    <div class="col offset-2">
                        <p class="lead text-muted mb-0 pl-3">{{ number_format(($stockEntry->inventory_on_hand * $stockEntry->product->wholesale_price), 0, '.', ' ') . ' Ft' }}</p>
                    </div>
                </div>
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