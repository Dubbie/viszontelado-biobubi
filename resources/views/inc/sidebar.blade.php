<div class="sidebar">
    <nav class="sidebar-nav">
        <div class="d-flex align-items-center justify-content-between text-center">
            <a class="navbar-brand mr-0" href="{{ url('/') }}">
                <img src="{{ url('/storage/logo.png') }}" alt="SemmiSzemét logo" style="max-width: 50%;">
                <p class="text-uppercase mb-0" style="line-height: 1; letter-spacing: 1px;">
                    <small class="font-weight-bold">Viszonteladó<br>Portál</small>
                </p>
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
                <a href="{{ action('OrderController@index') }}"
                   class="nav-link @if(Request::is('megrendelesek*')) active @endif d-flex align-items-center">
                    <span class="icon">
                        <i class="far fa-clipboard"></i>
                    </span>
                    <span class="flex-grow-1">Megrendelések</span>
                    {{--<span class="badge badge-primary">{{ count(Auth::user()->getVehicles()) }}</span>--}}
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ action('RevenueController@income') }}"
                   class="nav-link @if(Request::is('penzugy*')) active @endif d-flex align-items-center">
                    <span class="icon">
                        <i class="fas fa-dollar-sign"></i>
                    </span>
                    <span class="flex-grow-1">Pénzügy</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ action('OrderTodoController@index') }}"
                   class="nav-link @if(Request::is('teendok*')) active @endif d-flex align-items-center">
                    <span class="icon">
                        <i class="fas fa-clipboard-list"></i>
                    </span>
                    <span class="flex-grow-1">Teendők</span>
                    {{--<span class="badge badge-primary">{{ count(Auth::user()->getVehicles()) }}</span>--}}
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ action('UserController@profile') }}"
                   class="nav-link @if(Request::is('fiok*')) active @endif d-flex align-items-center">
                    <span class="icon">
                        <i class="far fa-user-circle"></i>
                    </span>
                    <span class="flex-grow-1">Fiókom</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ action('DocumentController@index') }}"
                   class="nav-link @if(Request::is('dokumentumok*')) active @endif d-flex align-items-center">
                    <span class="icon">
                        <i class="fas fa-file-alt"></i>
                    </span>
                    <span class="flex-grow-1">Dokumentumok</span>
                    {{--<span class="badge badge-primary">{{ count(Auth::user()->getVehicles()) }}</span>--}}
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ action('StockController@index') }}"
                   class="nav-link @if(Request::is('keszletem*')) active @endif d-flex align-items-center">
                    <span class="icon">
                        <i class="fas fa-boxes"></i>
                    </span>
                    <span class="flex-grow-1">Készletem</span>
                </a>
            </li>

            {{-- Admin dolgok --}}
            @if(Auth()->user() && Auth()->user()->admin)
                <li class="nav-title">Adminisztráció</li>

                {{-- Felhasználók --}}
                <li class="nav-item">
                    <a href="#user-collapse" class="nav-link @if(!Request::is('felhasznalok*')) collapsed @endif"
                       data-toggle="collapse">
                            <span class="icon">
                                <i class="fas fa-users"></i>
                            </span>
                        <span>Felhasználók</span>
                    </a>

                    <div id="user-collapse" class="collapse @if(Request::is('felhasznalok*')) show @endif">
                        <a class="nav-link @if(Request::is('felhasznalok')) active @endif"
                           href="{{ action('UserController@index') }}">
                            <span>Összes felhasználó</span>
                        </a>
                        <a class="nav-link @if(Request::is('felhasznalok/uj')) active @endif"
                           href="{{ action('UserController@create') }}">
                            <span>Új felhasználó</span>
                        </a>
                    </div>
                </li>

                <li class="nav-item">
                    <a href="{{ action('TrialProductController@listProducts') }}"
                       class="nav-link @if(Request::is('termekek*')) active @endif d-flex align-items-center">
                    <span class="icon">
                        <i class="fas fa-shopping-basket"></i>
                    </span>
                        <span class="flex-grow-1">Termékek</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ action('PostController@index') }}"
                       class="nav-link @if(Request::is('bejegyzesek*')) active @endif d-flex align-items-center">
                    <span class="icon">
                        <i class="fas fa-newspaper"></i>
                    </span>
                        <span class="flex-grow-1">Bejegyzések</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ action('StockController@adminIndex') }}"
                       class="nav-link @if(Request::is('kozponti-keszlet*')) active @endif d-flex align-items-center">
                    <span class="icon">
                        <i class="fas fa-warehouse"></i>
                    </span>
                        <span class="flex-grow-1">Központi készlet</span>
                    </a>
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