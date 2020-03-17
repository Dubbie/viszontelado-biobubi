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

    {{-- Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
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
            <p class="mb-0 px-5 my-2">
                <small>
                    <a href="https://dubbie.github.io">MihóDániel</a>
                    <span class="ml-4">&copy;{{ date('Y') }} BioBubi Viszonteladó Portál</span>
                </small>
            </p>
        </footer>
    </div>
</div>

{{-- Bootstrap --}}
<script src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"></script>
{{-- jQuery UI --}}
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
        integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU="
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"
        integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6"
        crossorigin="anonymous"></script>

{{-- Select2 --}}
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

{{-- Tagify --}}
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.polyfills.min.js"></script>

{{-- ChartJs --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.3/dist/Chart.min.js"></script>

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
        bsCustomFileInput.init();
    });
</script>
@yield('scripts')
</body>
</html>
