<div class="sidebar">
    <nav class="sidebar-nav">
        <div class="d-flex align-items-center justify-content-between text-center">
            <a class="navbar-brand mr-0" href="{{ url('/') }}">
                <img src="{{ url('/storage/shoplogo.png') }}" alt="BioBubi Kft. logo" style="max-width: 75%;">
                <p class="text-uppercase mb-0" style="line-height: 1.25; letter-spacing: 1px; font-size: 0.7rem;">
                    <span>Viszonteladó Portál</span>
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
                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-journal" fill="currentColor"
                             xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2z"></path>
                            <path
                                d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1z"></path>
                        </svg>
                    </span>
                    <span class="flex-grow-1">Megrendelések</span>
                    {{--<span class="badge badge-primary">{{ count(Auth::user()->getVehicles()) }}</span>--}}
                </a>
            </li>
            <li class="nav-item">
                <a href="#report-collapse" class="nav-link @if(!Request::is('riport*')) collapsed @endif"
                   data-toggle="collapse">
                    <span class="icon">
                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-people"
                             fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                  d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1h7.956a.274.274 0 0 0 .014-.002l.008-.002c-.002-.264-.167-1.03-.76-1.72C13.688 10.629 12.718 10 11 10c-1.717 0-2.687.63-3.24 1.276-.593.69-.759 1.457-.76 1.72a1.05 1.05 0 0 0 .022.004zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10c-1.668.02-2.615.64-3.16 1.276C1.163 11.97 1 12.739 1 13h3c0-1.045.323-2.086.92-3zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"></path>
                        </svg>
                    </span><span>Riportok</span>
                </a>

                <div id="report-collapse" class="collapse @if(Request::is('riport*')) show @endif">
                    <a class="nav-link @if(Request::is('riport/aktualis')) active @endif"
                       href="{{ action('ReportController@showQuick') }}">
                        <span>Aktuális riport</span>
                    </a>
                    <a class="nav-link @if(Request::is('riport/havi')) active @endif"
                       href="{{ action('ReportController@showMonthly') }}">
                        <span>Havi riportok</span>
                    </a>
                </div>
            </li>
            <li class="nav-item">
                <a href="{{ action('OrderTodoController@index') }}"
                   class="nav-link @if(Request::is('teendok*')) active @endif d-flex align-items-center">
                    <span class="icon">
                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-clipboard-check"
                             fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                  d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"></path>
                            <path fill-rule="evenodd"
                                  d="M9.5 1h-3a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h3a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3zm4.354 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0z"></path>
                        </svg>
                    </span>
                    <span class="flex-grow-1">Teendők</span>
                    {{--<span class="badge badge-primary">{{ count(Auth::user()->getVehicles()) }}</span>--}}
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ action('UserController@profile') }}"
                   class="nav-link @if(Request::is('fiok*')) active @endif d-flex align-items-center">
                    <span class="icon">
                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-person-square"
                             fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                  d="M14 1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1zM2 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"></path>
                            <path fill-rule="evenodd"
                                  d="M2 15v-1c0-1 1-4 6-4s6 3 6 4v1H2zm6-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"></path>
                        </svg>
                    </span>
                    <span class="flex-grow-1">Fiókom</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ action('StockController@index') }}"
                   class="nav-link @if(Request::is('keszletem*')) active @endif d-flex align-items-center">
                    <span class="icon">
                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-truck" fill="currentColor"
                             xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                  d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-3.998-.085A1.5 1.5 0 0 1 0 10.5v-7zm1.294 7.456A1.999 1.999 0 0 1 4.732 11h5.536a2.01 2.01 0 0 1 .732-.732V3.5a.5.5 0 0 0-.5-.5h-9a.5.5 0 0 0-.5.5v7a.5.5 0 0 0 .294.456zM12 10a2 2 0 0 1 1.732 1h.768a.5.5 0 0 0 .5-.5V8.35a.5.5 0 0 0-.11-.312l-1.48-1.85A.5.5 0 0 0 13.02 6H12v4zm-9 1a1 1 0 1 0 0 2 1 1 0 0 0 0-2zm9 0a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"></path>
                        </svg>
                    </span>
                    <span class="flex-grow-1">Készletem</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link @if(Request::is('kozpont/atutalasok*')) active @endif d-flex align-items-center"
                   href="{{ action('MoneyTransferController@index') }}">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor"
                             class="bi bi-credit-card" viewBox="0 0 16 16">
                            <path
                                d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v1h14V4a1 1 0 0 0-1-1H2zm13 4H1v5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V7z"/>
                            <path d="M2 10a1 1 0 0 1 1-1h1a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-1z"/>
                        </svg>
                    </span>
                    <span class="flex-grow-1">Átutalások</span>
                </a>
            </li>

            <li class="nav-item">
                <a href="{{ action('DocumentController@index') }}"
                   class="nav-link @if(Request::is('dokumentumok*')) active @endif d-flex align-items-center">
                    <span class="icon">
                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-file-text" fill="currentColor"
                             xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                  d="M4 0h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2zm0 1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H4z"></path>
                            <path fill-rule="evenodd"
                                  d="M4.5 10.5A.5.5 0 0 1 5 10h3a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zm0-2A.5.5 0 0 1 5 8h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zm0-2A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zm0-2A.5.5 0 0 1 5 4h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5z"></path>
                        </svg>
                    </span>
                    <span class="flex-grow-1">Kézikönyv</span>
                    {{--<span class="badge badge-primary">{{ count(Auth::user()->getVehicles()) }}</span>--}}
                </a>
            </li>

            <li class="nav-item">
                <a href="#customers-collapse" class="nav-link position-relative @if(!Request::is('ugyfelek*') && !Request::is('hivandok*')) collapsed @endif"
                   data-toggle="collapse">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" class="bi bi-person-badge" viewBox="0 0 16 16">
                          <path d="M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1h-3zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                          <path d="M4.5 0A2.5 2.5 0 0 0 2 2.5V14a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2.5A2.5 2.5 0 0 0 11.5 0h-7zM3 2.5A1.5 1.5 0 0 1 4.5 1h7A1.5 1.5 0 0 1 13 2.5v10.795a4.2 4.2 0 0 0-.776-.492C11.392 12.387 10.063 12 8 12s-3.392.387-4.224.803a4.2 4.2 0 0 0-.776.492V2.5z"/>
                        </svg>
                    </span>
                    <span>Ügyfelek</span>

                    @if(Auth::user()->calls()->count() > 0)
                        <span class="bi bi-icon text-success position-absolute" style="right: 40px">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-record-fill" viewBox="0 0 16 16">
                              <path fill-rule="evenodd" d="M8 13A5 5 0 1 0 8 3a5 5 0 0 0 0 10z"/>
                            </svg>
                        </span>
                    @endif
                </a>

                <div id="customers-collapse" class="collapse @if(Request::is('ugyfelek*') || Request::is('hivandok*')) show @endif">
                    <a class="nav-link @if(Request::is('ugyfelek*')) active @endif"
                       href="{{ action('CustomerController@index') }}">
                        <span>Összes ügyfél</span>
                    </a>
                    <a class="nav-link @if(Request::is('hivandok*')) active @endif"
                       href="{{ action('CustomerCallController@index') }}">
                        <span>Hívandók <span class="badge badge-pill badge-success">{{ Auth::user()->calls()->count() }}</span></span>
                    </a>
                </div>
            </li>

            @if(Auth::user()->worksheet()->count() > 0)
                <li class="nav-item">
                    <a href="#worksheetModal"
                       data-toggle="modal"
                       class="nav-link @if(Request::is('keszletem*')) active @endif d-flex align-items-center">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor"
                                 class="bi bi-clipboard" viewBox="0 0 16 16">
                                <path
                                    d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/>
                                <path
                                    d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/>
                            </svg>
                        </span>
                        <span class="flex-grow-1">Munkalap</span>
                        <span
                            class="badge badge-pill badge-success text-center">{{ Auth::user()->worksheet()->count() }}
                            db</span>
                    </a>
                </li>
            @endif

            {{-- Admin dolgok --}}
            @if(Auth::check() && Auth::user()->admin)
                <li class="nav-title">Adminisztráció</li>

                {{-- Felhasználók --}}
                <li class="nav-item">
                    <a href="#user-collapse" class="nav-link @if(!Request::is('felhasznalok*')) collapsed @endif"
                       data-toggle="collapse">
                        <span class="icon">
                            <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-people"
                                 fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                      d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8zm-7.978-1h7.956a.274.274 0 0 0 .014-.002l.008-.002c-.002-.264-.167-1.03-.76-1.72C13.688 10.629 12.718 10 11 10c-1.717 0-2.687.63-3.24 1.276-.593.69-.759 1.457-.76 1.72a1.05 1.05 0 0 0 .022.004zM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0zM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816zM4.92 10c-1.668.02-2.615.64-3.16 1.276C1.163 11.97 1 12.739 1 13h3c0-1.045.323-2.086.92-3zM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"></path>
                            </svg>
                        </span><span>Felhasználók</span>
                    </a>

                    <div id="user-collapse" class="collapse @if(Request::is('felhasznalok*')) show @endif">
                        <a class="nav-link @if(Request::is('felhasznalok')) active @endif"
                           href="{{ action('UserController@index') }}">
                            <span>Összes felhasználó</span>
                        </a>
                        <a class="nav-link @if(Request::is('felhasznalok/uj*')) active @endif"
                           href="{{ action('UserController@create') }}">
                            <span>Új felhasználó</span>
                        </a>
                    </div>
                </li>

                <li class="nav-item">
                    <a href="#products-collapse" class="nav-link @if(!Request::is('termekek*')) collapsed @endif"
                       data-toggle="collapse">
                        <span class="icon">
                            <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-basket" fill="currentColor"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                      d="M5.757 1.071a.5.5 0 0 1 .172.686L3.383 6h9.234L10.07 1.757a.5.5 0 1 1 .858-.514L13.783 6H15a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1v4.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 13.5V9a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h1.217L5.07 1.243a.5.5 0 0 1 .686-.172zM2 9v4.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V9H2zM1 7v1h14V7H1zm3 3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 4 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 6 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 8 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5z"></path>
                            </svg>
                        </span><span class="flex-grow-1">Termékek</span>
                    </a>
                    <div id="products-collapse" class="collapse @if(Request::is('termekek*')) show @endif">
                        <a class="nav-link @if(Request::is('termekek')) active @endif"
                           href="{{ action('TrialProductController@listProducts') }}">
                            <span>Összes termék</span>
                        </a>
                        <a class="nav-link @if(Request::is('termekek/csomagok*')) active @endif"
                           href="{{ action('BundleController@index') }}">
                            <span>Csomagok</span>
                        </a>
                    </div>
                </li>

                <li class="nav-item">
                    <a href="{{ action('PostController@index') }}"
                       class="nav-link @if(Request::is('bejegyzesek*')) active @endif d-flex align-items-center">
                        <span class="icon">
                            <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-newspaper"
                                 fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                      d="M0 2.5A1.5 1.5 0 0 1 1.5 1h11A1.5 1.5 0 0 1 14 2.5v10.528c0 .3-.05.654-.238.972h.738a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 1 1 0v9a1.5 1.5 0 0 1-1.5 1.5H1.497A1.497 1.497 0 0 1 0 13.5v-11zM12 14c.37 0 .654-.211.853-.441.092-.106.147-.279.147-.531V2.5a.5.5 0 0 0-.5-.5h-11a.5.5 0 0 0-.5.5v11c0 .278.223.5.497.5H12z"></path>
                                <path
                                    d="M2 3h10v2H2V3zm0 3h4v3H2V6zm0 4h4v1H2v-1zm0 2h4v1H2v-1zm5-6h2v1H7V6zm3 0h2v1h-2V6zM7 8h2v1H7V8zm3 0h2v1h-2V8zm-3 2h2v1H7v-1zm3 0h2v1h-2v-1zm-3 2h2v1H7v-1zm3 0h2v1h-2v-1z"></path>
                            </svg>
                        </span><span class="flex-grow-1">Bejegyzések</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="#hq-collapse" class="nav-link @if(!Request::is('kozpont*')) collapsed @endif"
                       data-toggle="collapse">
                        <span class="icon">
                            <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-shop" fill="currentColor"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                      d="M2.97 1.35A1 1 0 0 1 3.73 1h8.54a1 1 0 0 1 .76.35l2.609 3.044A1.5 1.5 0 0 1 16 5.37v.255a2.375 2.375 0 0 1-4.25 1.458A2.371 2.371 0 0 1 9.875 8 2.37 2.37 0 0 1 8 7.083 2.37 2.37 0 0 1 6.125 8a2.37 2.37 0 0 1-1.875-.917A2.375 2.375 0 0 1 0 5.625V5.37a1.5 1.5 0 0 1 .361-.976l2.61-3.045zm1.78 4.275a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 1 0 2.75 0V5.37a.5.5 0 0 0-.12-.325L12.27 2H3.73L1.12 5.045A.5.5 0 0 0 1 5.37v.255a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0zM1.5 8.5A.5.5 0 0 1 2 9v6h1v-5a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v5h6V9a.5.5 0 0 1 1 0v6h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1V9a.5.5 0 0 1 .5-.5zM4 15h3v-5H4v5zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-3zm3 0h-2v3h2v-3z"></path>
                            </svg>
                        </span><span class="flex-grow-1">Központ</span>
                    </a>

                    <div id="hq-collapse" class="collapse @if(Request::is('kozpont*')) show @endif">
                        <a class="nav-link @if(Request::is('kozpont/penzugy*')) active @endif"
                           href="{{ action('RevenueController@hqFinance') }}">
                            <span>Pénzügyek</span>
                        </a>
                        <a class="nav-link @if(Request::is('kozpont/keszlet*')) active @endif"
                           href="{{ action('CentralStockController@index') }}">
                            <span>Raktárkészlet</span>
                        </a>
                    </div>
                </li>

                <li>
                    <a href="{{ action('RegionController@index') }}"
                       class="nav-link @if(Request::is('regiok*')) active @endif">
                        <span class="icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor"
                                 class="bi bi-geo-alt" viewBox="0 0 16 16">
                                <path
                                    d="M12.166 8.94c-.524 1.062-1.234 2.12-1.96 3.07A31.493 31.493 0 0 1 8 14.58a31.481 31.481 0 0 1-2.206-2.57c-.726-.95-1.436-2.008-1.96-3.07C3.304 7.867 3 6.862 3 6a5 5 0 0 1 10 0c0 .862-.305 1.867-.834 2.94zM8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10z"/>
                                <path d="M8 8a2 2 0 1 1 0-4 2 2 0 0 1 0 4zm0 1a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                            </svg>
                        </span><span>Régiók</span>
                    </a>
                </li>
            @endif
        </ul>

        @auth
            <ul class="sidebar-bottom-menu">
                <x-environment/>
                <li class="nav-item dropup">
                    <a id="navbarDropdown" class="nav-link " href="#" role="button"
                       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        <h5 class="d-block font-weight-bold text-dark mb-0">
                            {{ Auth::user()->name }}
                        </h5>
                    </a>
                    <span class="d-block mb-2 has-tooltip" data-toggle="tooltip" data-placement="right"
                          title="Marketing egyenleg">
                        {{ resolve('App\Subesz\MoneyService')->getFormattedMoney(Auth::user()->marketingBalance()) }} Ft
                    </span>

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
