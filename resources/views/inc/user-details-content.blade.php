<div class="row">
    <div class="col-md-8">
        <h3 class="font-weight-bold mb-1">{{ $user->name }} @if($user->admin) <span class="badge badge-success">Admin</span> @endif</h3>
        <h5 class="font-weight-bold text-muted mb-0">{{ $user->email }}</h5>
    </div>
    <div class="col-md-4 text-right">
        <a href="{{ action('UserController@edit', ['userId' => $user->id]) }}" class="btn btn-sm btn-outline-secondary">Szerkesztés</a>
        <a href="{{ action('OrderController@index', ['filter-reseller' => $user->id]) }}" class="btn btn-sm btn-link">Megrendelések</a>
    </div>
</div>

<p class="font-weight-bold mb-1 mt-4">Hozzárendelt irányítószámok</p>
<ul class="list-unstyled">
    @foreach($user->zips as $zip)
        <li>{{ $zip->zip }}</li>
    @endforeach
</ul>
