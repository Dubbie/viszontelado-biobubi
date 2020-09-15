@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Csomagok</h1>
            </div>
            <div class="col text-right">
                <a href="{{ action('BundleController@create') }}" class="btn btn-teal shadow-sm">Csomag
                    hozzáadása</a>
            </div>
        </div>
        <div class="card card-body">
            @if(count($products) > 0)
                @php /** @var \App\Product $product */ @endphp
                @foreach($products as $product)
                    <div class="row @if($products->last() != $product) mb-4 @endif">
                        <div class="col-5">
                            <div class="d-flex align-items-center">
                                <img src="{{ $product->picture_url }}" alt="{{ $product->name }}"
                                     class="d-block img-thumbnail mr-3"
                                     style="width: 48px; height: 48px; object-fit: cover">
                                <p class="lead font-weight-bold mb-0">{{ $product->name }} <span class="text-muted">(sku: {{ $product->sku }})</span></p>
                            </div>
                        </div>
                        <div class="col-7">
                            <div class="bg-muted rounded-lg p-3 border">
                                @php /** @var \App\BundleProduct $subProduct */ @endphp
                                @foreach($product->subProducts as $subProduct)
                                    <div class="d-flex align-items-center @if($product->subProducts->last() != $subProduct) mb-2 @endif">
                                        <img src="{{ $subProduct->product->picture_url }}"
                                             alt="{{ $subProduct->product->name }}"
                                             class="d-block img-thumbnail mr-3"
                                             style="width: 48px; height: 48px; object-fit: cover">
                                        <p class="mb-0"><b>{{ $subProduct->product_qty }}
                                                db</b> {{ $subProduct->product->name }} <span class="text-muted">(sku: {{ $subProduct->product_sku }})</span></p>
                                    </div>
                                @endforeach

                                <div class="d-flex mt-4">
                                    <a href="{{ action('BundleController@edit', $product->sku) }}"
                                       class="btn btn-success btn-sm">Csomag szerkesztése</a>
                                    <form action="{{ action('BundleController@destroy', $product->sku) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-muted btn-del-bundle">Csomag törlése</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="lead mb-4">Még nincs létrehozva egy csomag sem.</p>
                <p class="mb-0">
                    <a href="{{ action('BundleController@create') }}" class="btn btn-teal shadow-sm">Csomag
                        hozzáadása</a>
                </p>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(() => {
            // Megj. törlés
            const $btnDelComment = $('.btn-del-bundle');
            $btnDelComment.on('click', e => {
                if (!confirm('Biztosan törölni szeretnéd a csomagot? Ez a folyamat nem visszafordítható.')) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection