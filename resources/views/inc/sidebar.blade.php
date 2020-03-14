<div class="sidebar-sticky d-flex flex-column vh-100 w-100">
    {{-- Nagy navi --}}
    <a href="/" class="d-none d-md-flex justify-content-center text-center my-5 text-dark">
        <p class="mb-0">Viszonteladó<b class="d-block">Portál</b></p>
    </a>
    {{-- Telós gomb --}}
    <a href="#!" class="nav-link btn-toggle-sidebar d-md-none text-decoration-none text-dark p-4 text-right">
        <span class="icon icon-lg">
            <i class="fas fa-bars"></i>
        </span>
    </a>
    {{-- Menü --}}
    <ul class="nav flex-column align-items-center">
        <li class="nav-item mb-0 mb-md-3">
            <a href="/" class="nav-link has-tooltip @if(Request::is('/')) active @endif"
               data-toggle="tooltip" data-placement="right" title="Főoldal">
                <span class="icon icon-lg d-none d-md-inline-flex">
                    <i class="fas fa-home"></i>
                </span>
                <span class="d-md-none">Főoldal</span>
            </a>
        </li>
        <li class="nav-item mb-0 mb-md-3">
            <a href="{{ action('OrderController@index') }}" class="nav-link has-tooltip @if(Request::is('megrendelesek*')) active @endif"
               data-toggle="tooltip" data-placement="right" title="Megrendelések">
                <span class="icon icon-lg d-none d-md-inline-flex">
                    <i class="fas fa-file-invoice-dollar"></i>
                </span>
                <span class="d-md-none">Megrendelések</span>
            </a>
        </li>

        @if(Auth()->user()->admin)
            <li class="nav-item mb-0 mb-md-3">
                <a href="{{ action('UserController@index') }}" class="nav-link has-tooltip @if(Request::is('felhasznalok*')) active @endif"
                   data-toggle="tooltip" data-placement="right" title="Felhasználók">
                <span class="icon icon-lg d-none d-md-inline-flex">
                    <i class="fas fa-users"></i>
                </span>
                    <span class="d-md-none">Felhasználók</span>
                </a>
            </li>
        @endif
    </ul>

    @auth
        <div class="user-details mt-auto text-center mb-5">
            <div class="dropdown">
                <a href="#!" id="userMenuDropdown" role="button" data-toggle="dropdown">
                    <span class="icon icon-lg">
                        <i class="fas fa-user"></i>
                    </span>
                </a>
                <div class="dropdown-menu" role="menu" aria-labelledby="userMenuDropdown">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item">Kijelentkezés</button>
                    </form>
                </div>
            </div>
        </div>
    @endauth
</div>