@extends('layouts.app')

@section('content')
    <div class="container">
        {{--                <p class="mb-0">--}}
        {{--                    <a href="{{ action('UserController@index') }}"--}}
        {{--                       class="btn-muted font-weight-bold text-decoration-none">--}}
        {{--                        <span class="icon icon-sm">--}}
        {{--                            <i class="fas fa-arrow-left"></i>--}}
        {{--                        </span>--}}
        {{--                        <span>Vissza a felhasználókhoz</span>--}}
        {{--                    </a>--}}
        {{--                </p>--}}
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Régiók</h1>
            </div>
            <div class="col text-md-right">
                <a href="{{ action('RegionController@create') }}" class="btn btn-teal">Új régió</a>
            </div>
        </div>
        <div class="card card-body">
            @if(count($regions) == 0)
                {{-- TODO --}}
                <p>Nincsenek régiók</p>
            @else
                @php /** @var \App\Region $region */ @endphp
                <div class="row d-none d-md-flex">
                    <div class="col-md-5">
                        <small class="font-weight-semiboldbold">Régió</small>
                    </div>
                    <div class="col-md-4">
                        <small class="font-weight-semiboldbold">Viszonteladó</small>
                    </div>
                    <div class="col-md-2 text-md-right">
                        <small class="font-weight-semiboldbold">Ir. számok</small>
                    </div>
                    <div class="col-md-1">
                        <small class="font-weight-semiboldbold"></small>
                    </div>
                </div>
                @foreach($regions as $region)
                    <div class="row mt-2">
                        <div class="col-md-5"><p class="mb-0 font-weight-bold text-truncate">{{ $region->name }}</p>
                        </div>
                        <div class="col-md-4"><p class="mb-0 text-truncate">{{ $region->user->name }}</p></div>
                        <div class="col-md-2"><p class="text-md-right mb-0">{{ $region->zips_count }} db</p></div>
                        <div class="col-md-1 text-md-right">
                            <a href="{{ action('RegionController@edit', $region) }}"
                               class="btn btn-sm btn-muted p-0 position-relative">
                                <span class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                         class="bi bi-pencil-square" viewBox="0 0 16 16">
                                        <path
                                            d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456l-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                        <path fill-rule="evenodd"
                                              d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/>
                                    </svg>
                                </span>
                            </a>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection
