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
                        <h1 class="font-weight-bold mb-4">Felhasználó szerkesztése</h1>
                    </div>
                </div>
                <div class="card card-body">
                    <form action="{{ action('UserController@update', ['userId' => $user->id]) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <h5 class="font-weight-bold mb-1">Alapvető adatok</h5>
                        <div class="form-group">
                            <label for="u-name">Név <small class="text-muted">Ez a viszonteladó neve</small></label>
                            <input type="text" id="u-name" name="u-name" class="form-control" value="{{ $user->name }}" required>
                        </div>
                        <div class="form-group">
                            <label for="u-email">E-mail cím <small class="text-muted">Ez a viszonteladó e-mail címe amivel be tud majd lépni</small></label>
                            <input type="email" id="u-email" name="u-email" class="form-control" value="{{ $user->email }}" required>
                        </div>
                        <div class="form-group">
                            <label for="u-zip">Hozzárendelt irányítószámok <small class="text-muted">Az itt megadott irányítószámokra fog szűrni a rendszer</small></label>
                            <input type="text" id="u-zip" name="u-zip" class="form-control" value="{{ $zips }}">
                        </div>

                        <h5 class="font-weight-bold mb-1">Billingo adatok</h5>
                        <div class="form-group">
                            <label for="u-billingo-public-key">Nyilvános kulcs</label>
                            <input type="text" id="u-billingo-public-key" name="u-billingo-public-key" class="form-control" value="{{ $user->billingo_public_key }}">
                        </div>
                        <div class="form-group">
                            <label for="u-billingo-private-key">Privát kulcs</label>
                            <textarea type="text" id="u-billingo-private-key" name="u-billingo-private-key" class="form-control">{{ $user->billingo_private_key }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="u-block-uid">Számlatömb azonosító <small class="text-muted"><a href="https://app.billingo.hu/beallitasok/szamlazo/szamlatomb">Ezen</a> az oldalon található, a Tömb API ID-t kell ide beírni.</small></label>
                            <input type="text" id="u-block-uid" name="u-block-uid" class="form-control" value="{{ $user->block_uid }}">
                        </div>
                        <div class="form-group mb-0 text-right">
                            <button type="submit" class="btn btn-sm btn-success">Felhasználó frissítése</button>
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