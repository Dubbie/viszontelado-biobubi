@extends('layouts.full')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-6 offset-md-3 col-xl-4 offset-xl-4">
                <form method="POST" action="{{ route('login') }}" class="card card-body">
                    @csrf
                    <h5 class="font-weight-bold text-uppercase mb-4">Bejelentkezés</h5>
                    <div class="form-group">
                        <label for="email" class="d-block d-md-none font-weight-bold text-small mb-1">E-mail cím</label>
                        <input id="email" type="email" class="form-control form-control-sm @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="E-mail cím" autofocus>
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group mb-1">
                        <label for="password" class="d-block d-md-none font-weight-bold text-small mb-1">Jelszó</label>
                        <input id="password" type="password" class="form-control form-control-sm @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Jelszó">
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="remember"><small>Maradjak bejelentkezve</small></label>
                        </div>
                    </div>

                    <div class="form-group d-flex justify-content-between align-items-center mb-0">
                        <button type="submit" class="btn btn-sm btn-primary">Bejelentkezés</button>

                        @if (Route::has('password.request'))
                            <a class="btn btn-sm btn-link" href="{{ route('password.request') }}">Elfelejtettem a jelszavam</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
