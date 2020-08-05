@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="font-weight-bold mb-4">Fiókom</h1>

        <div class="row">
            <div class="col-md-6">
                <div class="card card-body mb-4 mb-md-0">
                    <div class="row mb-2">
                        <div class="col-md-8">
                            <h4 class="font-weight-bold mb-2">Billingo Integráció</h4>
                            <p class="text-muted">A számlák automatikus kiállításához szükséges adatokat az adminisztrátor tudja állítani.</p>
                        </div>
                        <div class="col-md-4">
                            <a href="https://app.billingo.hu/document/list">
                                <img src="{{ url('/storage/billingo.png') }}" alt="Billingo logo" class="d-block mw-100">
                            </a>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <p class="font-weight-bold mb-0">Billingo API csatlakozás: </p>
                        </div>
                        <div class="col-md-6 text-md-right">
                            <span class="font-weight-bold {{ $billingo ? 'text-success' : 'text-danger' }}">{{ $billingo ? 'Sikeres' : 'Sikertelen' }}</span>
                        </div>
                    </div>

                    @if(!$billingo)
                        <div class="alert alert-warning rounded-lg mt-4 mb-0">
                            <p class="mb-0">Kérjük vegye fel a kapcsolatot egy adminisztrátorral, hogy működjön az automatikus számla kiállítás.</p>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-body">
                    <h4 class="font-weight-bold mb-4">Jelszóváltás</h4>

                    <form action="{{ action('UserController@updatePassword') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="old-password">Jelenlegi jelszó</label>
                            <input type="password" id="old-password" name="old-password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Új jelszó</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Új jelszó megint</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                        </div>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-success">Jelszó frissítése</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection