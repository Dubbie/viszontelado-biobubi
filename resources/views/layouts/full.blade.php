<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'BioBubi | Viszonteladó portál') }}</title>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    {{-- FontAwesome Kit --}}
    <script src="https://kit.fontawesome.com/9dd01b031a.js"></script>
</head>
<body class="auth">
<div class="page-wrapper">
    <div class="container">
        <div class="row no-gutters mt-5">
            <div class="col-md-6 order-1 order-md-0">
                <img src="{{ url('/storage/recycle.jpg') }}" alt="" class="mw-100">
            </div>
            <div class="col-md-6">
                <main class="page-content p-5">
                    <h3 class="font-weight-bold mb-1 text-primary">BioBubi Viszonteladó Portál</h3>
                    <p class="text-muted mb-4">Kérjük az oldal használatához először jelentkezzen be.</p>

                    @yield('content')
                </main>
            </div>
        </div>
    </div>
</div>
<footer>
    <div class="container">
        <p class="text-center">
            <small>
                <span class="ml-4">&copy;{{ date('Y') }} BioBubi Viszonteladó Portál</span>
                <span class="px-2 text-muted">•</span>
                <span><a href="https://dubbie.github.io">@dubbie</a></span>
            </small>
        </p>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"
        integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6"
        crossorigin="anonymous"></script>

@yield('custom-scripts')
</body>
</html>
