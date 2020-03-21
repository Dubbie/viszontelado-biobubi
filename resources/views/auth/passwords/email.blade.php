@extends('layouts.full')

@section('content')
    <h4 class="font-weight-bold mb-2">Jelszó visszaállítása</h4>

    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="form-group">
            <label for="email">E-mail cím</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
            @error('email')
            <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group mb-0">
            <button type="submit" class="btn btn-success font-weight-normal">Visszaállító link küldése</button>
        </div>
    </form>
@endsection
