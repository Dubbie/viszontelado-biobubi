{{--<div class="sidebar-sticky d-flex flex-column vh-100 w-100">--}}
    {{-- Nagy navi --}}
    {{--<a href="/" class="d-none d-md-flex justify-content-center text-center my-5 text-dark">--}}
        {{--<p class="mb-0">Viszonteladó<b class="d-block">Portál</b></p>--}}
    {{--</a>--}}
    {{-- Telós gomb --}}
    {{--<a href="#!" class="nav-link btn-toggle-sidebar d-md-none text-decoration-none text-dark p-4 text-right">--}}
        {{--<span class="icon icon-lg">--}}
            {{--<i class="fas fa-bars"></i>--}}
        {{--</span>--}}
    {{--</a>--}}
    {{-- Menü --}}
    {{--<ul class="nav flex-column align-items-center">--}}
        {{--<li class="nav-item mb-0 mb-md-3">--}}
            {{--<a href="/" class="nav-link has-tooltip @if(Request::is('/')) active @endif"--}}
               {{--data-toggle="tooltip" data-placement="right" title="Főoldal">--}}
                {{--<span class="icon icon-lg d-none d-md-inline-flex">--}}
                    {{--<i class="fas fa-home"></i>--}}
                {{--</span>--}}
                {{--<span class="d-md-none">Főoldal</span>--}}
            {{--</a>--}}
        {{--</li>--}}
        {{--<li class="nav-item mb-0 mb-md-3">--}}
            {{--<a href="{{ action('OrderController@index') }}" class="nav-link has-tooltip @if(Request::is('megrendelesek*')) active @endif"--}}
               {{--data-toggle="tooltip" data-placement="right" title="Megrendelések">--}}
                {{--<span class="icon icon-lg d-none d-md-inline-flex">--}}
                    {{--<i class="fas fa-file-invoice-dollar"></i>--}}
                {{--</span>--}}
                {{--<span class="d-md-none">Megrendelések</span>--}}
            {{--</a>--}}
        {{--</li>--}}

        {{--@if(Auth()->user()->admin)--}}
            {{--<li class="nav-item mb-0 mb-md-3">--}}
                {{--<a href="{{ action('UserController@index') }}" class="nav-link has-tooltip @if(Request::is('felhasznalok*')) active @endif"--}}
                   {{--data-toggle="tooltip" data-placement="right" title="Felhasználók">--}}
                {{--<span class="icon icon-lg d-none d-md-inline-flex">--}}
                    {{--<i class="fas fa-users"></i>--}}
                {{--</span>--}}
                    {{--<span class="d-md-none">Felhasználók</span>--}}
                {{--</a>--}}
            {{--</li>--}}
        {{--@endif--}}
    {{--</ul>--}}

    {{--@auth--}}
        {{--<div class="user-details mt-auto text-center mb-5">--}}
            {{--<div class="dropdown">--}}
                {{--<a href="#!" id="userMenuDropdown" role="button" data-toggle="dropdown">--}}
                    {{--<span class="icon icon-lg">--}}
                        {{--<i class="fas fa-user"></i>--}}
                    {{--</span>--}}
                {{--</a>--}}
                {{--<div class="dropdown-menu" role="menu" aria-labelledby="userMenuDropdown">--}}
                    {{--<form action="{{ route('logout') }}" method="POST">--}}
                        {{--@csrf--}}
                        {{--<button type="submit" class="dropdown-item">Kijelentkezés</button>--}}
                    {{--</form>--}}
                {{--</div>--}}
            {{--</div>--}}
        {{--</div>--}}
    {{--@endauth--}}
{{--</div>--}}

<div class="sidebar">
    <nav class="sidebar-nav">
        <div class="d-flex align-items-center justify-content-between">
            <a class="navbar-brand" href="{{ url('/') }}">
                {{--<img src="//placehold.it/48/48" alt="">--}}
                <p class="mb-0">BioBubi<span class="d-block text-primary">Viszonteladó Portál</span></p>
            </a>
            <button type="button" class="btn-mobile-nav d-block d-md-none">
                    <span class="icon">
                        <i class="fas fa-bars"></i>
                    </span>
            </button>
        </div>

        <ul class="sidebar-menu">
            <li class="nav-title">Menü</li>
            <li class="nav-item">
                <a href="{{ action('OrderController@index') }}" class="nav-link @if(Request::is('megrendelesek*')) active @endif d-flex align-items-center">
                        <span class="icon">
                            <i class="far fa-clipboard"></i>
                        </span>
                    <span class="flex-grow-1">Megrendelések</span>
                    {{--<span class="badge badge-primary">{{ count(Auth::user()->getVehicles()) }}</span>--}}
                </a>
            </li>

            {{-- Admin dolgok --}}
            @if(Auth()->user() && Auth()->user()->admin)
                <li class="nav-title">Adminisztráció</li>

                {{-- Felhasználók --}}
                <li class="nav-item">
                    <a href="#user-collapse" class="nav-link @if(!Request::is('felhasznalok*')) collapsed @endif" data-toggle="collapse">
                            <span class="icon">
                                <i class="fas fa-users"></i>
                            </span>
                        <span>Felhasználók</span>
                    </a>

                    <div id="user-collapse" class="collapse @if(Request::is('felhasznalok*')) show @endif">
                        <a class="nav-link @if(Request::is('felhasznalok')) active @endif" href="{{ action('UserController@index') }}">
                            <span>Összes felhasználó</span>
                        </a>
                        <a class="nav-link @if(Request::is('felhasznalok/uj')) active @endif" href="{{ action('UserController@create') }}">
                            <span>Új felhasználó</span>
                        </a>
                    </div>
                </li>
            @endif
        </ul>

        @auth
            <ul class="sidebar-bottom-menu">
                <li class="nav-item dropup">
                    <a id="navbarDropdown" class="nav-link " href="#" role="button"
                       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        <span class="d-block font-weight-bold text-dark">{{ Auth::user()->name }}</span>
                    </a>

                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{ route('logout') }}"
                           onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                            Kijelentkezés
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST"
                              style="display: none;">
                            @csrf
                        </form>
                    </div>
                </li>
            </ul>
        @endauth
    </nav>
</div>