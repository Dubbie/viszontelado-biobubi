@php /** @var \App\User $user */ @endphp
<div class="row">
    <div class="col-md-10">
        <h3 class="font-weight-bold mb-0">{{ $user->name }} @if($user->admin) <span
                    class="badge badge-success">Admin</span> @endif</h3>
        <h5 class="font-weight-bold text-muted mb-2">{{ $user->email }}</h5>
        <p class="text-muted mb-0">{{ $user->vat_id == env('AAM_VAT_ID') ? 'Alanyi Adómentes' : 'Nem Alanyi Adómentes' }}</p>
    </div>
</div>

<div class="row mt-5">
    <div class="col-md-5">
        <h4 class="font-weight-bold mb-3">Billingo Integráció</h4>
        <p class="mb-4 text-muted">A számlák automatikus kiállításához szükséges a <b>Billingo</b> rendszerével való összekapcsolás.<br>Ezt a jobb felül található <b>Szerkesztés</b> gombra kattintva tudod megtenni.</p>
    </div>
    <div class="col-md-6 offset-md-1">
        <a href="https://app.billingo.hu/document/list" class="d-block">
            <img src="{{ url('/storage/billingo.png') }}" alt="Billingo logo" class="d-block mw-100" style="width: 100px;">
        </a>
        <div class="row">
            <div class="col-md-7">
                <p class="font-weight-bold mb-0">API kulcs</p>
            </div>
            <div class="col-md-5">
                <p class="has-tooltip mb-0 {{ $user->billingo_api_key ? 'text-success' : 'text-danger' }}" data-toggle="tooltip" data-placement="left"
                   title="Biztonsági okokból ezeket csak a szerkesztés menüpont alatt láthatod.">{{ $user->billingo_api_key ? 'Van megadva' : 'Nincs megadva' }}</p>
            </div>
            <div class="col-md-7">
                <p class="font-weight-bold mb-0">Számlatömb azonosító</p>
            </div>
            <div class="col-md-5">
                <p class="has-tooltip mb-0 {{ $user->block_uid ? 'text-success' : 'text-danger' }}" data-toggle="tooltip" data-placement="left"
                   title="Biztonsági okokból ezeket csak a szerkesztés menüpont alatt láthatod.">{{ $user->block_uid ? 'Van megadva' : 'Nincs megadva' }}</p>
            </div>
        </div>
    </div>
</div>

<p class="h5 font-weight-bold mt-4 mb-3">Irányítószámok</p>
<div class="row">
    <div class="col-md-5">
        <p class="mb-0 text-muted">Ezek az irányítószámok lettek hozzárendelve a felhasználóhoz.<br>Új megrendeléskor nézi a rendszer, ez alapján találja meg a helyes viszonteladót.</p>
    </div>
    <div class="col-md-6 offset-md-1">
        <div class="row">
            @if(count($user->zips) > 0)
                @foreach($user->zips->sortBy('zip') as $zip)
                    <div class="col-md-4 col-lg-3">
                        <p class="font-weight-bold mb-0">{{ $zip->zip }}</p>
                    </div>
                @endforeach
            @else
                <div class="col">
                    <div class="alert alert-info">
                        <p class="lead mb-0">A felhasználóhoz nem tartozik egy irányítószám sem, ezért nem fog kapni megrendeléseket automatikusan.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@if($user->details)
    <div class="row mt-5">
        <div class="col-md-5">
            <p class="h5 font-weight-bold mb-1">Számlázási cím</p>
            <p class="mb-3 text-muted">A viszonteladó számlázási címe.</p>
        </div>
        <div class="col-md-6 offset-md-1">
            <p class="h5 text-secondary font-weight-bold mb-0">{{ $user->details->billing_name }}</p>
            <p class="mb-2 text-muted font-weight-bold">{{ $user->details->billing_tax_number ?? 'Nem lett megadva adószám' }}</p>

            @if($user->details->billingAddress)
                <p class="mb-0">{{ $user->details->billingAddress->getFormattedAddress() }}</p>
            @endif
        </div>
    </div>
    @if($user->details->shipping_name || $user->details->shippingAddress)
        <div class="row mt-4">
            <div class="col-md-5">
                <p class="h5 text-secondary font-weight-bold mb-1">Kiszállítási cím</p>
                <p class="mb-3 text-muted">A viszonteladó kiszállítási címe.</p>
            </div>
            <div class="col-md-6 offset-md-1">
                <p class="h5 mb-0 font-weight-bold">{{ $user->details->shipping_name }}</p>

                @if($user->details->shippingAddress)
                    <p class="mb-0">{{ $user->details->shippingAddress->getFormattedAddress() }}</p>
                @endif
            </div>
        </div>
    @else
        <div class="row">
            <div class="offset-md-6 col">
                <div class="alert alert-info mt-4">
                    <p class="mb-2">A kiszállítási adatok nem lettek kitöltve, csak a számlázási adatok.</p>
                    <p class="mb-0">
                        <a href="{{ action('UserController@edit', $user) }}" class="btn btn-sm btn-primary">Szerkesztés</a>
                    </p>
                </div>
            </div>
        </div>
    @endif
@endif

<div class="row mt-5">
    <div class="col-md-5">
        <p class="h5 font-weight-bold mb-1">Kiszállítások</p>
    </div>
    <div class="col-md-6 offset-md-1">
        @if($user->deliveries_count > 0)
            <p class="mb-1">Összesen kiszállítva: <b>{{ $user->deliveries_count }}</b></p>
            <p class="mb-0">Ebben a hónapban: <b>{{ $user->getDeliveryCountThisMonth() }}</b></p>
        @else
            <p class="mb-0">A felhasználó még egy megrendelést sem szállított ki a portálon keresztül.</p>
        @endif
    </div>
</div>