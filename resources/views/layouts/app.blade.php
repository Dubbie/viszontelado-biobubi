<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- Bundle --}}
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>
<body>
<div class="page-wrapper">
    @include('inc.sidebar')

    <div class="d-flex d-md-none mobile-nav align-items-center justify-content-between">
        <a href="/" class="h5 text-dark"><b>Viszonteladó Portál</b></a>
        <button type="button" class="btn-mobile-nav">
            <span class="icon">
                <i class="fas fa-bars"></i>
            </span>
        </button>
    </div>

    <div id="page-content-overlay" style="display: none;"></div>
    <div class="page-content">
        @include('inc.messages')
        @yield('content')

        <footer id="footer">
           <div class="container py-3">
               <p class="mb-0">
                   <small>
                       <span class="ml-4">&copy;{{ date('Y') }} SemmiSzemét Viszonteladó Portál</span>
                       <span class="px-2 text-muted">•</span>
                       <span><a href="https://dubbie.github.io">@dubbie</a></span>
                   </small>
               </p>
           </div>
        </footer>
    </div>
</div>

{{-- Bundle --}}
<script src="{{ mix('js/app.js') }}"></script>
<script src="{{ asset('js/ckeditor.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>

{{-- Tooltipek a sidebarnak --}}
<script>
    $(document).ready(function() {
        function showSidebar() {
            body.classList.add('sidebar-on');
            sidebar.classList.add('show');
            pageOverlay.show();
        }
        function hideSidebar() {
            body.classList.remove('sidebar-on');
            sidebar.classList.remove('show');
            pageOverlay.hide();
        }
        var body = $('body')[0];
        var sidebar = $('.sidebar')[0];
        var btnMobileToggle = $('.btn-mobile-nav');
        var pageOverlay = $('#page-content-overlay');
        btnMobileToggle.on('click', function (e) {
            if (body.classList.contains('sidebar-on')) {
                hideSidebar();
            } else {
                showSidebar();
            }
        });
        pageOverlay.on('click', function (e) {
            hideSidebar();
        });

        $('.has-tooltip[data-toggle="tooltip"]').tooltip();

        // Inicializálja a custom file inputot.
        // bsCustomFileInput.init();
    });
</script>
@yield('scripts')
</body>
</html>
