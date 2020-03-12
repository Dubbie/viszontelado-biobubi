<h3 class="font-weight-bold mb-1">{{ $user->name }}</h3>
<h5 class="font-weight-bold text-muted mb-4">{{ $user->email }}</h5>

<p class="font-weight-bold mb-1">Hozzárendelt irányítószámok</p>
<ul class="list-unstyled">
    @foreach($user->zips as $zip)
        <li>{{ $zip->zip }}</li>
    @endforeach
</ul>
