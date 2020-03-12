<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    {{-- FontAwesome Kit --}}
    <script src="https://kit.fontawesome.com/9dd01b031a.js"></script>

    {{-- Tagify CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div class="page-wrapper mw-100">
    <nav id="sidebar" class="sidebar">
        @include('inc.sidebar')
    </nav>
    <main id="page-content" role="main" class="admin-content min-vh-100 p-0 p-md-5">
        {{-- Overlay hack --}}
        <div id="mobile-overlay" class="d-none"></div>
        {{-- Telefonos menü --}}
        <div class="d-flex d-md-none justify-content-between align-items-center mb-0 p-4">
            <a href="/" class="h5 text-uppercase text-dark text-decoration-none mb-0">Viszonteladó<b>Portál</b></a>
            <button type="button" class="btn-icon btn-toggle-sidebar d-block d-md-none">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        @include('inc.messages')
        @yield('content')
    </main>
</div>
<script src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
        crossorigin="anonymous"></script>

{{-- Tagify --}}
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>

{{-- Tooltipek a sidebarnak --}}
<script>
    $(function () {
        const $btnToggleSidebar = $('.btn-toggle-sidebar');
        const elSidebar = document.getElementById('sidebar');
        const elMobileOverlay = document.getElementById('mobile-overlay');
        // Menügomb bind
        function toggleSidebar() {
            elSidebar.classList.toggle('show');
            elMobileOverlay.classList.toggle('d-none');
        }
        function init() {
            // Tooltipek
            $('.has-tooltip').tooltip();
            // Sidebar toggle with button and page content
            $btnToggleSidebar.add($(elMobileOverlay)).on('click', (e) => {
                toggleSidebar();
            });
        }
        init();
    });
</script>
@yield('scripts')
</body>
</html>
