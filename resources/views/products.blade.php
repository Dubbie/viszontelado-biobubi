@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Termékek</h1>
            </div>
        </div>
        <div class="card card-body">
            <table class="table table-hover table-sm table-responsive-md table-borderless mb-0">
                <thead>
                <tr>
                    <th scope="col"></th>
                    <th scope="col">Név</th>
                    <th scope="col">Cikkszám</th>
                    <th scope="col" class="text-right">Bruttó alapár</th>
                    <th scope="col">Állapot</th>
                    <th scope="col" class="text-center">Próbacsomag</th>
                </tr>
                </thead>
                <tbody>
                @foreach($products as $product)
                    <tr>
                        <td>
                            <img src="{{ $product->allImages->mainImage }}" alt="{{ $product->productDescriptions[0]->name }}"
                                 class="d-block img-thumbnail" style="width: 48px; height: 48px; object-fit: cover">
                        </td>
                        <td class="align-middle"><b>{{ $product->productDescriptions[0]->name }}</b></td>
                        <td class="align-middle">{{ $product->sku }}</td>
                        <td class="align-middle text-right">{{ number_format(round($product->price * 1.27), 0, '.', ' ') . ' Ft' }}</td>
                        <td class="align-middle">{{ $product->status == 1 ? 'Engedélyezve' : 'Letiltva' }}</td>
                        <td class="align-middle text-center">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input ch-trial-product"
                                       id="product-{{ $product->sku }}" data-product-sku="{{ $product->sku }}"
                                        {{ in_array($product->sku, $trials) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="product-{{ $product->sku }}"></label>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $( () => {
            const $chTrial = $('.ch-trial-product');

            $chTrial.on('change', e => {
                console.log(e.currentTarget.dataset.productSku);
                $.ajax('/api/termek/atkapcsol/' + e.currentTarget.dataset.productSku, {
                    method: 'POST',
                }).done(response => {
                    console.log(response);
                }).fail(response => {
                    alert('Hiba történt a próbacsomag átállításakor!');
                });
            });
        });
    </script>
@endsection