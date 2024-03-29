@extends('layouts.app')

@section('content')
    @php /** @var \App\Product $product */ @endphp
    <div class="container">
        <div class="row">
            <div class="col-lg-6 offset-lg-3">
                <p class="mb-3">
                    <a href="{{ route('hq.stock.index') }}" class="btn-muted font-weight-bold text-decoration-none">
                        <span class="icon icon-sm">
                            <i class="fas fa-arrow-left"></i>
                        </span>
                        <span>Vissza a központi készlethez</span>
                    </a>
                </p>
                <div class="card card-body p-md-5">
                    <h5 class="font-weight-bold mb-4">Leolvasott termék</h5>
                    <figure class="figure mb-0">
                        <div class="row">
                            <div class="col-4">
                                <img class="figure-img img-fluid rounded" src="{{ $product->picture_url }}" alt="A feltölteni kívánt termék képe...">
                            </div>
                            <div class="col-8">
                                <p class="mb-0 font-weight-bold h5">{{ $product->name }}</p>
                                <p class="text-muted mb-0"><small class="font-weight-semibold">Cikkszám: <span class="font-weight-bold">{{ $product->sku }}</span></small></p>
                                <p class="text-muted">
                                    <small class="font-weight-semibold">Készleten: <span id="original-amount" class="text-dark font-weight-bold">{{ $inventoryOnHand }} db</span></small>
                                </p>
                            </div>
                        </div>
                    </figure>

                    <hr class="mb-4">

                    <div class="text-center">
                        <p class="lead font-weight-bold">Mit szeretnél tenni a termékkel?</p>
                        <a href="{{ action('CentralStockController@incoming', $product->sku) }}" class="btn font-weight-semibold btn-outline-dark d-inline-flex align-items-center">
                            <span class="icon text-muted mr-1">
                                <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-shop" fill="currentColor"
                                 xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                      d="M2.97 1.35A1 1 0 0 1 3.73 1h8.54a1 1 0 0 1 .76.35l2.609 3.044A1.5 1.5 0 0 1 16 5.37v.255a2.375 2.375 0 0 1-4.25 1.458A2.371 2.371 0 0 1 9.875 8 2.37 2.37 0 0 1 8 7.083 2.37 2.37 0 0 1 6.125 8a2.37 2.37 0 0 1-1.875-.917A2.375 2.375 0 0 1 0 5.625V5.37a1.5 1.5 0 0 1 .361-.976l2.61-3.045zm1.78 4.275a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0 1.375 1.375 0 1 0 2.75 0V5.37a.5.5 0 0 0-.12-.325L12.27 2H3.73L1.12 5.045A.5.5 0 0 0 1 5.37v.255a1.375 1.375 0 0 0 2.75 0 .5.5 0 0 1 1 0zM1.5 8.5A.5.5 0 0 1 2 9v6h1v-5a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v5h6V9a.5.5 0 0 1 1 0v6h.5a.5.5 0 0 1 0 1H.5a.5.5 0 0 1 0-1H1V9a.5.5 0 0 1 .5-.5zM4 15h3v-5H4v5zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-3zm3 0h-2v3h2v-3z"></path>
                                </svg>
                            </span>
                            <span>Központi készlethez adom</span>
                        </a>
                        <p class="my-3 text-muted">vagy</p>
                        <a href="{{ action('CentralStockController@toReseller', $product->sku) }}" class="btn font-weight-semibold btn-outline-dark d-inline-flex align-items-center">
                            <span class="icon text-muted mr-1">
                                <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-shop" fill="currentColor"
                                     xmlns="http://www.w3.org/2000/svg">
                                  <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                                </svg>
                            </span>
                            <span>Viszonteladónak adom</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection