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
        <p class="font-weight-bold mb-1">Hozzárendelt irányítószámok</p>
    </div>
    <div class="col-md-7">
        <ul class="list-unstyled">
            @if(count($user->zips) > 0)
                @foreach($user->zips as $zip)
                    <li class="h2 mb-0">{{ $zip->zip }}</li>
                @endforeach
            @else
                <li>A felhasználóhoz nem tartozik egy irányítószám sem</li>
            @endif
        </ul>
    </div>
</div>

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
