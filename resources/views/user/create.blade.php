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
                    <form id="user-form" action="{{ action('UserController@store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-lg-4">
                                <h5 class="font-weight-bold mb-1">Alapvető adatok</h5>
                            </div>
                            <div class="col-lg-8">
                                <div class="form-group">
                                    <label for="u-name">Név</label>
                                    <input type="text" id="u-name" name="u-name" class="form-control" required>
                                    <small class="form-text text-muted">Ez a viszonteladó neve</small>
                                </div>
                                <div class="form-group">
                                    <label for="u-email">E-mail cím</label>
                                    <input type="email" id="u-email" name="u-email" class="form-control" required>
                                    <small class="form-text text-muted">Ez a viszonteladó e-mail címe amivel be tud majd lépni</small>
                                </div>
                                <div class="form-group">
                                    <label for="u-password">Jelszó</label>
                                    <input type="password" id="u-password" name="u-password" class="form-control" required>
                                    <small class="form-text text-muted">Az itt megadott jelszóval tud majd belépni</small>
                                </div>
                                <div class="form-group">
                                    <label for="u-zip">Hozzárendelt irányítószámok </label>
                                    <input type="text" id="u-zip" name="u-zip" class="form-control" required>
                                    <small class="form-text text-muted">Az itt megadott irányítószámokra fog szűrni a rendszer</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <h5 class="font-weight-bold mb-1">Billingo integráció</h5>
                            </div>
                            <div class="col-lg-8">
                                <div class="form-group">
                                    <label for="u-billingo-public-key">Nyilvános kulcs</label>
                                    <input type="text" id="u-billingo-public-key" name="u-billingo-public-key" class="form-control">
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="form-group">
                                    <label for="u-billingo-private-key">Privát kulcs</label>
                                    <textarea type="text" id="u-billingo-private-key" name="u-billingo-private-key" class="form-control"></textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="form-group">
                                    <label for="u-block-uid">Számlatömb azonosító</label>
                                    <input type="text" id="u-block-uid" name="u-block-uid" class="form-control">
                                    <small class="form-text text-muted"><a href="https://app.billingo.hu/beallitasok/szamlazo/szamlatomb">Ezen</a> az oldalon található, a Tömb API ID-t kell ide beírni.</small>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="u-aam" name="u-aam">
                                        <label class="custom-control-label" for="u-aam">A felhasználó által kiállított számlák Alanyi Adómentesek.</label>
                                    </div>
                                </div>

                                <div id="billingo-test-results"></div>
                            </div>
                        </div>

                        <div class="form-group mb-0 text-right">
                            <button type="button" id="btn-billingo-api-test" class="btn btn-sm btn-outline-secondary mr-2">
                                <span class="text">Billingo integráció teszelése</span>
                                <span class="loading" style="display: none;">
                                    <span class="spinner-border spinner-border-sm text-primary" role="status">
                                      <span class="sr-only">Loading...</span>
                                    </span>
                                    <span class="ml-2">Csatlakozás folyamatban...</span>
                                </span>
                            </button>
                            <button type="submit" class="btn btn-sm btn-success">
                                <span class="text">Felhasználó létrehozása</span>
                                <span class="loading" style="display: none;">
                                    <span class="spinner-border spinner-border-sm text-primary" role="status">
                                      <span class="sr-only">Loading...</span>
                                    </span>
                                    <span class="ml-2">Létrehozás...</span>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection