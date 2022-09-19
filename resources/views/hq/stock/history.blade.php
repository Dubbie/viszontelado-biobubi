@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Központi készlet</h1>
            </div>
            @if(Auth::user()->admin)
                <div class="col text-right">
                    <a href="{{ action('CentralStockController@index') }}"
                       class="btn btn-muted">Aktuális</a>
                    <a href="#stockMovement" data-toggle="modal" data-target="#stockMovement"
                       class="btn btn-teal shadow-sm">Készlet mozgatása</a>
                </div>
            @endif
        </div>

        <div class="card card-body p-md-5 mb-4">
            <div class="row pb-1 border-bottom mb-2">
                <div class="col-6 col-lg-2"><p class="mb-0"><small class="font-weight-bold">Viszonteladó</small></p></div>
                <div class="d-none d-lg-block col-lg-6"><p class="mb-0"><small class="font-weight-bold">Termék</small></p></div>
                <div class="col-6 text-right text-lg-left col-lg-2"><p class="mb-0"><small class="font-weight-bold">Darabszám</small></p></div>
                <div class="d-none d-lg-block col-lg-2"><p class="mb-0"><small class="font-weight-bold">Dátum</small></p></div>
            </div>
            @foreach($movements as $move)
                <div class="row @if($movements->last() != $move) border-bottom pb-2 mb-2 @endif">
                    <div class="col-12 col-lg-2">{{ $move->reseller->name ?? 'BioBubi Központ' }}</div>
                    <div class="col-9 col-lg-6">
                        <p class="font-weight-bold mb-0">{{ $move->product->name }}</p>
                    </div>
                    <div class="col-3 text-right text-lg-left col-lg-2">
                        <p class="mb-0 @if($move->quantity < 0) text-danger @endif">{{ $move->quantity > 0 ? '+' . $move->quantity : $move->quantity }}</p>
                    </div>
                    <div class="col-12 col-lg-2">
                        <p class="mb-0 text-muted">{{ $move->created_at->format('Y.m.d H:i') }}</p>
                    </div>
                </div>
            @endforeach
            <div class="pagination mt-4 mb-0">
                {{ $movements->links() }}
            </div>
        </div>
    </div>

    @include('modal.stock.stock-movement')
@endsection

@section('scripts')
@endsection