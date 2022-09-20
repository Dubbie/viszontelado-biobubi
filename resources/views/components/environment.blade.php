@if(env('APP_ENV') != 'production')
        <li class="nav-item mb-2">
            <div class="card card-body text-warning-pastel px-2 py-2 mx-4">@if(env('APP_ENV') == 'local') Fejlesztői @else Teszt @endif környezet</div>
        </li>
@endif