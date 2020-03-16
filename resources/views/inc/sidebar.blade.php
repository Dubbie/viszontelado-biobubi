<div class="sidebar">
    <nav class="sidebar-nav">
        <div class="d-flex align-items-center justify-content-between">
            <a class="navbar-brand mr-0" href="{{ url('/') }}">
                {{--<img src="//placehold.it/48/48" alt="">--}}
                <p class="mb-0">BioBubi<small class="d-block font-weight-bold text-primary">Viszonteladó Portál</small></p>
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