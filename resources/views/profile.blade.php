@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="font-weight-bold mb-4">Fiókom</h1>

        <div class="row">
            <div class="col-md-6">
                <div class="card card-body">
                    <h5 class="font-weight-bold mb-4">Jelszóváltás</h5>

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