<div class="rs-row form-row align-items-end mb-4">
    <div class="col-md-5">
        <div class="form-group mb-0">
            <label for="rs-add-stock-{{ time() }}">Termék</label>
            <select name="rs-add-stock[]" id="rs-add-stock-{{ time() }}" class="custom-select">
                @php /** @var \App\CentralStock $cs */ @endphp
                @foreach($centralStock as $cs)
                    <option value="{{ $cs->product->sku }}"
                            data-gross-price="{{ $cs->product->gross_price }}"
                            data-max-qty="{{ $cs->inventory_on_hand }}">{{ $cs->product->name }}
                        (Cikkszám: {{ $cs->product->sku }})
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group mb-0">
            <label for="rs-add-stock-qty-{{ time() }}">Mennyiség</label>
            <div class="input-group">
                <input type="number" name="rs-add-stock-qty[]" id="rs-add-stock-qty-{{ time() }}" class="form-control"
                       value="1" required>
                <div class="input-group-append">
                    <span class="input-group-text">db</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group text-right mb-0">
            <p class="text-muted mb-2">
                <small>Br. összeg</small>
            </p>
            <span class="form-control px-0 text-muted border-0 rs-gross-price">0 Ft</span>
        </div>
    </div>
    <div class="col-md-2">
        <div class="form-group text-right mb-0">
            <p class="text-muted mb-2">
                <small>Összesen</small>
            </p>
            <span class="form-control px-0 text-muted border-0 rs-total-price">0 Ft</span>
        </div>
    </div>
    <div class="col-auto text-right" style="width: 60px">
        @if(!$first)
            <button class="btn btn-del mb-1 ml-auto btn-remove-rs-row has-tooltip" type="button">
                <svg width="32px" height="32px" viewBox="0 0 16 16" class="bi bi-x" fill="currentColor"
                     xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                          d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"></path>
                </svg>
            </button>
        @endif
    </div>
</div>