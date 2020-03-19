@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <p class="mb-0">
                    <a href="{{ action('UserController@index') }}" class="btn-muted font-weight-bold text-decoration-none">
                        <span class="icon icon-sm">
                            <i class="fas fa-arrow-left"></i>
                        </span>
                        <span>Vissza a felhasználókhoz</span>
                    </a>
                </p>
                <div class="row">
                    <div class="col">
                        <h1 class="font-weight-bold mb-4">Új felhasználó</h1>
                    </div>
                </div>
                <div class="card card-body">
                    <form action="{{ action('UserController@store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="u-name">Név <small class="text-muted">Ez a viszonteladó neve</small></label>
                            <input type="text" id="u-name" name="u-name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="u-email">E-mail cím <small class="text-muted">Ez a viszonteladó e-mail címe amivel be tud majd lépni</small></label>
                            <input type="email" id="u-email" name="u-email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="u-password">Jelszó <small class="text-muted">Az itt megadott jelszóval tud majd belépni</small></label>
                            <input type="password" id="u-password" name="u-password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="u-zip">Hozzárendelt irányítószámok <small class="text-muted">Az itt megadott irányítószámokra fog szűrni a rendszer</small></label>
                            <input type="text" id="u-zip" name="u-zip" class="form-control" required>
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-success">Felhasználó létrehozása</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const inputZip = document.getElementById('u-zip');

        const tagify = new Tagify(inputZip);
    </script>
@endsection