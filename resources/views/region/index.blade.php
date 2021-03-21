@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
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
                        @foreach($regions as $region)
                            <div class="row">
                                <div class="col-md-5"><p class="mb-0 text-truncate">{{ $region->name }}</p></div>
                                <div class="col-md-5"><p class="mb-0 text-truncate">{{ $region->user->name }}</p></div>
                                <div class="col-md-2"><p class="text-md-right mb-0">{{ $region->zips()->count() }}
                                        ir.szám</p></div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
