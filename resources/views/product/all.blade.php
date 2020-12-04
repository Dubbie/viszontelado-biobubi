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
                    <th scope="col" class="text-right">Beszer. ár</th>
                    <th scope="col" class="text-right">Nagyker ár</th>
                    <th scope="col">Állapot</th>
                    <th scope="col"></th>
                </tr>
                </thead>
                <tbody>
                @php /** @var \App\Product $product */ @endphp
                @foreach($products as $product)
                    <tr>
                        <td>
                            <img src="{{ $product->picture_url }}" alt="{{ $product->name }}"
                                 class="d-block img-thumbnail" style="width: 48px; height: 48px; object-fit: cover">
                        </td>
                        <td class="align-middle"><b>{{ $product->name }}</b></td>
                        <td class="align-middle">{{ $product->sku }}</td>
                        <td class="align-middle text-right">{{ number_format($product->gross_price, 0, '.', ' ') . ' Ft' }}</td>
                        <td class="align-middle text-right">{{ number_format($product->purchase_price, 0, '.', ' ') . ' Ft' }}</td>
                        <td class="align-middle text-right">{{ number_format($product->wholesale_price, 0, '.', ' ') . ' Ft' }}</td>
                        <td class="align-middle">{{ $product->status ? 'Engedélyezve' : 'Letiltva' }}</td>
                        <td class="align-middle text-right">
                            <a href="#editProduct" data-toggle="modal" class="btn btn-muted btn-edit-product"
                               data-product-img="{{ $product->picture_url }}"
                               data-product-name="{{ $product->name }}" data-product-sku="{{ $product->sku }}"
                               data-gross-price="{{ $product->gross_price }}"
                               data-purchase-price="{{ $product->purchase_price }}"
                               data-wholesale-price="{{ $product->wholesale_price }}">
                                <span class="bs-icon">
                                    <i class="fas fa-edit"></i>
                                </span>
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @include('modal.edit-product')
@endsection

@section('scripts')
    <script>
        $( () => {
            const $btnEditProduct = $('.btn-edit-product');
            const imgProduct = document.getElementById('ep-product-img');
            const productName = document.getElementById('ep-product-name');
            const productSku = document.getElementById('ep-product-sku');
            const grossPrice = document.getElementById('ep-gross-price');
            const purchasePrice = document.getElementById('ep-purchase-price');
            const wholesalePrice = document.getElementById('ep-wholesale-price');
            const hiddenSku = document.getElementById('ep-hidden-product-sku');

            $btnEditProduct.on('click', e => {
                const btn = e.currentTarget;

                imgProduct.src = btn.dataset.productImg;
                productName.innerText = btn.dataset.productName;
                productSku.innerText = btn.dataset.productSku;
                hiddenSku.value = btn.dataset.productSku;
                productName.innerText = btn.dataset.productName;
                productName.innerText = btn.dataset.productName;
                productName.innerText = btn.dataset.productName;
                grossPrice.value = (btn.dataset.grossPrice.toLocaleString()) + ' Ft';
                purchasePrice.value = btn.dataset.purchasePrice.toLocaleString();
                wholesalePrice.value = btn.dataset.wholesalePrice.toLocaleString();
            });
        });
    </script>
@endsection
