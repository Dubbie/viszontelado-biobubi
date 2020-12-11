<div class="cs-row form-row align-items-end mb-4">
    <div class="col-md-6">
        <div class="form-group mb-0">
            <label for="cs-new-product-{{ time() }}">Termék</label>
            <select name="cs-new-product[]" id="cs-new-product-{{ time() }}" class="custom-select">
                @php /** @var \App\Product $product */ @endphp
                @foreach($products as $product)
                    <option value="{{ $product->sku }}" data-gross-price="{{ $product->wholesale_price }}">{{ $product->name }} (Cikkszám: {{ $product->sku }})</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md">
        <div class="form-group mb-0">
            <label for="cs-new-product-qty-{{ time() }}">Mennyiség</label>
            <div class="input-group">
                <input type="text" name="cs-new-product-qty[]" id="cs-new-product-qty-{{ time() }}" class="form-control" value="1" required>
                <div class="input-group-append">
                    <span class="input-group-text">db</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md">
        <div class="form-group text-right mb-0">
            <p class="text-muted mb-2"><small>Br. összeg</small></p>
            <span class="form-control px-0 text-muted border-0 cs-gross-price">0 Ft</span>
        </div>
    </div>
    <div class="col-md">
        <div class="form-group text-right mb-0">
            <p class="text-muted mb-2"><small>Összesen</small></p>
            <span class="form-control px-0 text-muted border-0 cs-total-price">0 Ft</span>
        </div>
    </div>
    <div class="col-auto text-right" style="width: 60px">
        @if(!$first)
            <button class="btn btn-del mb-1 ml-auto btn-remove-cs-row has-tooltip" type="button">
                <svg width="32px" height="32px" viewBox="0 0 16 16" class="bi bi-x" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"></path>
                </svg>
            </button>
        @endif
    </div>
</div>