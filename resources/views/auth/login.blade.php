@extends('layouts.full')

@section('content')
    <div class="row">
        <div class="col-xl-10">
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <h4 class="font-weight-bold mb-2">Bejelentkezés</h4>
                <div class="form-group mb-2">
                    <label for="email" class="d-block d-md-none font-weight-bold text-small mb-1">E-mail cím</label>

                    <div class="inner-addon">
                        <span class="icon">
                            <i class="far fa-envelope"></i>
                        </span>
                        <input id="email" type="email" class="form-control  @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="E-mail cím" autofocus>
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>
                </div>

                <div class="form-group mb-2">
                    <label for="password" class="d-block d-md-none font-weight-bold text-small mb-1">Jelszó</label>
                    <div class="inner-addon">
                        <span class="icon">
                            <i class="fas fa-key"></i>
                        </span>
                        <input id="password" type="password" class="form-control  @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Jelszó">
                    </div>
                    @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>

                <div class="form-group mb-4">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="custom-control-label" for="remember"><small>Maradjak bejelentkezve</small></label>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-sm btn-primary">Bejelentkezés</button>

                    {{--@if (Route::has('password.request'))--}}
                        {{--<a class="btn btn-sm btn-link text-muted ml-3" href="{{ route('password.request') }}">Elfelejtettem a jelszavam</a>--}}
                    {{--@endif--}}
                </div>
            </form>
        </div>
    </div>
@endsection
