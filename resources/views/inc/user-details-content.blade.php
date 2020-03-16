<div class="row">
    <div class="col-md-8">
        <h3 class="font-weight-bold mb-1">{{ $user->name }}</h3>
        <h5 class="font-weight-bold text-muted mb-4">{{ $user->email }}</h5>
    </div>
    <div class="col-md-4 text-right">
        <a href="{{ action('UserController@edit', ['userId' => $user->id]) }}" class="btn btn-sm btn-outline-secondary">Szerkesztés</a>
    </div>
</div>

<p class="font-weight-bold mb-1">Hozzárendelt irányítószámok</p>
<ul class="list-unstyled">
    @foreach($user->zips as $zip)
        <li>{{ $zip->zip }}</li>
    @endforeach
</ul>
