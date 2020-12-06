@php /** @var \App\User $user */ @endphp
<div class="row">
    <div class="col-md-8">
        <h3 class="font-weight-bold mb-1">{{ $user->name }} @if($user->admin) <span
                    class="badge badge-success">Admin</span> @endif</h3>
        <h5 class="font-weight-bold text-muted mb-0">{{ $user->email }}</h5>
    </div>
    <div class="col-md-4 text-right">
        <a href="{{ action('UserController@edit', ['userId' => $user->id]) }}"
           class="btn btn-sm btn-block btn-outline-secondary">Szerkesztés</a>
        <a href="{{ action('OrderController@index', ['filter-reseller' => $user->id]) }}"
           class="btn btn-sm btn-block btn-link">Megrendelések</a>
    </div>
</div>

<h5 class="font-weight-bold mb-1 mt-4">Billingo API</h5>
<div class="row">
    <div class="col-md-5">
        <p class="font-weight-bold mb-0">API kulcs</p>
    </div>
    <div class="col-md-7">
        <p class="has-tooltip mb-0 {{ $user->billingo_api_key ? 'text-success' : 'text-danger' }}" data-toggle="tooltip" data-placement="left"
           title="Biztonsági okokból ezeket csak a szerkesztés menüpont alatt jelenítjük meg.">{{ $user->billingo_api_key ? 'Van megadva' : 'Nincs megadva' }}</p>
    </div>
    <div class="col-md-5">
        <p class="font-weight-bold mb-0">Számlatömb azonosító</p>
    </div>
    <div class="col-md-7">
        <p class="has-tooltip mb-0 {{ $user->block_uid ? 'text-success' : 'text-danger' }}" data-toggle="tooltip" data-placement="left"
           title="Biztonsági okokból ezeket csak a szerkesztés menüpont alatt jelenítjük meg.">{{ $user->block_uid ? 'Van megadva' : 'Nincs megadva' }}</p>
    </div>
    <div class="col-md-5">
        <p class="font-weight-bold mb-0">Alanyi Adómentes</p>
    </div>
    <div class="col-md-7">
        <p class="mb-0">{{ $user->vat_id == env('AAM_VAT_ID') ? 'Igen' : 'Nem' }}</p>
    </div>
</div>

<div class="row mt-5">
    <div class="col-md-5">
        <p class="h5 font-weight-bold mb-1">Irányítószámok</p>
        <p class="mb-0 text-muted">Ezek az irányítószámok lettek hozzárendelve a felhasználóhoz.<br>Új megrendeléskor nézi a rendszer.</p>
    </div>
    <div class="col-md-7">
        <div class="row">
            @if(count($user->zips) > 0)
                @foreach($user->zips as $zip)
                    <div class="col-md-3">
                        <p class="h5 mb-0">{{ $zip->zip }}</p>
                    </div>
                @endforeach
            @else
                <div class="col">A felhasználóhoz nem tartozik egy irányítószám sem, ezért nem fog kapni megrendeléseket automatikus módon.</div>
            @endif
        </div>
    </div>
</div>

@if($user->details)
    <div class="row mt-5">
        <div class="col-md-5">
            <p class="font-weight-bold mb-1">Számlázási cím</p>
        </div>
        <div class="col-md-7">
            <p class="h5 mb-0 font-weight-bold">{{ $user->details->billing_name }}</p>
            <p class="mb-2 text-muted font-weight-bold">{{ $user->details->billing_tax_number ?? 'Adószám nem lett megadva' }}</p>

            @if($user->details->billingAddress)
                <p class="mb-0">{{ $user->details->billingAddress->getFormattedAddress() }}</p>
            @endif
        </div>
    </div>
    <div class="row mt-5">
        <div class="col-md-5">
            <p class="font-weight-bold mb-1">Kiszállítási cím</p>
        </div>
        <div class="col-md-7">
            <p class="h5 mb-0 font-weight-bold">{{ $user->details->shipping_name }}</p>
            <p class="mb-2 text-muted font-weight-bold">{{ $user->details->billing_tax_number ?? 'Adószám nem lett megadva' }}</p>

            @if($user->details->billingAddress)
                <p class="mb-0">{{ $user->details->billingAddress->getFormattedAddress() }}</p>
            @endif
        </div>
    </div>
@endif

<p class="h5 font-weight-bold mt-4 mb-2">Készlet</p>
@if($user->stock()->count() > 0)
    @php /** @var \App\Stock $item */ @endphp
    <table class="table table-sm table-borderless mb-0">
        <thead>
        <tr>
            <th>Termék neve</th>
            <th>Cikkszám</th>
            <th class="text-right">Raktár</th>
            <th class="text-right">Foglalt</th>
            <th class="text-right">Kiszállítva</th>
        </tr>
        </thead>
        <tbody>
        @foreach($user->stock as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->sku }}</td>
                <td class="text-right">{{ $item->inventory_on_hand }} db</td>
                <td class="text-right">{{ $item->getBookedCount() }} db</td>
                <td class="text-right">{{ $item->getSoldCount() }} db</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <a href="{{ action('StockController@edit', $user) }}" class="btn btn-success btn-sm mt-4">Viszonteladó készletének szerkesztése</a>
@else
    <p class="mb-0 text-muted">Nincs a viszonteladóhoz még készlet nyilvántartás létrehozva.</p>
@endif

<div class="row mt-5">
    <div class="col-md-5">
        <p class="font-weight-bold mb-1">Kiszállítások</p>
    </div>
    <div class="col-md-7">
        <ul class="list-unstyled">
            @if($user->deliveries_count > 0)
                <li>Kiszállítva: {{ $user->deliveries_count }}</li>
            @else
                <li>A felhasználó még egy megrendelést sem szállított ki a portálon keresztül.</li>
            @endif
        </ul>
    </div>
</div>
