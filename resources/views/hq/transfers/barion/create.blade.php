@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-12 col-md">
                <p class="mb-0">
                    <a href="{{ url()->previous(action('MoneyTransferController@index')) }}"
                        class="btn-muted font-weight-bold text-decoration-none">
                        <span class="icon icon-sm">
                            <i class="fas fa-arrow-left"></i>
                        </span>
                        <span>Vissza az előző oldalra</span>
                    </a>
                </p>
                <h1 class="font-weight-bold mb-4">Átutalás rögzítése</h1>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <div class="card card-body text-center">
                    <h3 class="mb-4 font-weight-bold">Táblázat export kiválasztása</h3>

                    <div class="row">
                        <div class="col-md-10 offset-md-1">
                            <form action="{{ action('BarionController@store') }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label for="mt-table-export" class="d-flex align-items-center mb-0">Táblázat export
                                        *</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="mt-table-export"
                                            id="mt-table-export">
                                        <label class="custom-file-label" for="mt-table-export" data-browse="Böngészés">Fájl
                                            kiválasztása</label>
                                    </div>
                                </div>

                                <div class="form-group mt-4 mb-0 text-right">
                                    <button type="submit" class="btn btn-sm btn-success">Létrehozás
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            bsCustomFileInput.init()
        })
    </script>
@endsection
