<div class="row">
    <div class="col-md-8">
        <h3 class="font-weight-bold mb-1">{{ $user->name }} @if($user->admin) <span class="badge badge-success">Admin</span> @endif</h3>
        <h5 class="font-weight-bold text-muted mb-0">{{ $user->email }}</h5>
    </div>
    <div class="col-md-4 text-right">
        <a href="{{ action('UserController@edit', ['userId' => $user->id]) }}" class="btn btn-sm btn-block btn-outline-secondary">Szerkesztés</a>
        <a href="{{ action('OrderController@index', ['filter-reseller' => $user->id]) }}" class="btn btn-sm btn-block btn-link">Megrendelések</a>
    </div>
</div>

<div class="row mt-5">
    <div class="col-md-4">
        <p class="font-weight-bold mb-1">Hozzárendelt irányítószámok</p>
    </div>
    <div class="col-md-8">
        <ul class="list-unstyled">
            @foreach($user->zips as $zip)
                <li class="h2 mb-0">{{ $zip->zip }}</li>
            @endforeach
        </ul>
    </div>
</div>

<div class="row mt-5">
    <div class="col-md-4">
        <p class="font-weight-bold mb-1">Kiszállítások</p>
    </div>
    <div class="col-md-8">
        <ul class="list-unstyled">
            @foreach($user->deliveries as $delivery)
                <li>
                    <a href="{{ action('OrderController@show', $delivery->order->inner_resource_id) }}">{{ $delivery->order->lastname }} {{ $delivery->order->firstname }}</a>
                    <p class="mb-0"><span>{{ $delivery->order->getFormattedAddress() }}</span></p>
                    <small class="d-block text-muted">Megrendelve: {{ $delivery->order->created_at }}</small>
                    <small class="d-block text-muted">Kiszállítva: {{ $delivery->delivered_at }}</small>
                </li>
            @endforeach
        </ul>
    </div>
</div>
