@extends('layouts.app')

@section('content')
    <div id="marketing-page" class="container">
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Marketing</h1>
            </div>
        </div>

        <div class="card card-body">
            <p>Henló</p>
            <p class="mb-0">
                <a href="#newMarketingResult" data-toggle="modal" class="btn btn-outline-primary">Új jelentés</a>
            </p>
        </div>
    </div>

    @include('modal.new-marketing-result')
@endsection
