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
                        <div class="col-md-1">
                            <div class="d-flex justify-content-md-end">
                                {{-- Szerkesztés --}}
                                <a href="{{ action('RegionController@edit', $region) }}"
                                   class="btn btn-sm btn-muted p-0 d-inline-flex align-items-center has-tooltip"
                                   data-toggle="tooltip" title="Szerkesztés" data-placement="left">
                                    <span class="icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                             fill="currentColor"
                                             class="bi bi-pencil-square" viewBox="0 0 16 16">
                                            <path
                                                d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456l-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                            <path fill-rule="evenodd"
                                                  d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/>
                                        </svg>
                                    </span>
                                    <span class="d-block d-md-none">Szerkesztés</span>
                                </a>

                                {{-- Törlés --}}
                                <form class="form-del-region" action="{{ action('RegionController@destroy', $region) }}"
                                      method="POST">
                                    @csrf
                                    @method('delete')
                                    <button type="submit"
                                            class="btn btn-sm btn-muted p-0 d-inline-flex align-items-center ml-2 ml-md-0 has-tooltip"
                                            data-toggle="tooltip" title="Törlés" data-placement="left">
                                        <span class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                 fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                                <path
                                                    d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                                <path fill-rule="evenodd"
                                                      d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4L4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                            </svg>
                                        </span>
                                        <span class="d-block d-md-none">Törlés</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(() => {
            $('.form-del-region').on('submit', e => {
                if (!confirm('Biztosan törlöd a régiót? Ez a folyamat nem visszafordítható és ennek következtében, az érintett viszonteladó megrendelései nem fognak megérkezni az törlendő régióból.')) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection
