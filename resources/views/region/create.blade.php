@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <p class="mb-0">
                    <a href="{{ url()->previous(action('RegionController@index')) }}"
                       class="btn-muted font-weight-bold text-decoration-none">
                                        <span class="icon icon-sm">
                                            <i class="fas fa-arrow-left"></i>
                                        </span>
                        <span>Vissza</span>
                    </a>
                </p>
                <div class="row">
                    <div class="col">
                        <h1 class="font-weight-bold mb-4">Új régió</h1>
                    </div>
                </div>
                <div class="card card-body">
                    <form action="{{ action('RegionController@store') }}" method="POST">
                        @csrf
                        {{-- Régi megnevezése --}}
                        <div class="form-row">
                            <div class="col-md-4">
                                <label for="region-name" class="font-weight-bold mb-1">Régió megnevezése *</label>
                                <p class="text-muted">Ez alapján fogod tudni azonosítani a régió listában.</p>
                            </div>
                            <div class="col offset-md-1">
                                <input id="region-name" name="region-name" type="text" class="form-control" required>
                            </div>
                        </div>

                        {{-- Régióhoz tartozó viszonteladó --}}
                        <div class="form-row mt-4">
                            <div class="col-md-4">
                                <label for="region-user-id" class="font-weight-bold mb-1">Viszonteladó *</label>
                                <p class="text-muted">A viszonteladók a régiók alapján fogják kapni
                                    megrendeléseiket.</p>
                            </div>
                            <div class="col offset-md-1">
                                <select name="region-user-id" id="region-user-id" class="custom-select">
                                    <option value="" hidden selected disabled>Kérlek válassz...</option>
                                    @foreach($resellers as $reseller)
                                        <option value="{{ $reseller->id }}">{{ $reseller->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Régióhoz tartozó irányítószámok --}}
                        <div class="form-row mt-4">
                            <div class="col-md-4">
                                <label for="u-zip" class="font-weight-bold mb-1">Irányítószámok *</label>
                                <p class="text-muted">Az irányítószámok alapján dől el, melyik régióhoz tartozik a
                                    megrendelés.</p>
                            </div>
                            <div class="col offset-md-1">
                                <input type="text" id="u-zip" name="region-zips" class="form-control" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col-md-8 offset-md-4 text-md-right">
                                <button type="submit" class="btn btn-success">Régió mentése</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(() => {
            $('#region-user-id').select2();
        });
    </script>
@endsection
