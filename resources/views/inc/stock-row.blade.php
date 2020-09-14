<div class="stock-row form-row align-items-end mb-3" id="stock-row-{{ time() }}" style="display: none;">
    <div class="col">
        <div class="form-group mb-0">
            <label for="stock-item-sku[{{ time() }}]">Termék *</label>
            <select name="stock-item-sku[{{ time() }}]" id="stock-item-sku[{{ time() }}]" class="custom-select" required>
                @foreach($items as $item)
                    <option value="{{ $item->sku }}|{{ $item->productDescriptions[0]->name }}">{{ $item->productDescriptions[0]->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-2">
        <div class="from-group mb-0">
            <label for="stock-item-count[{{ time() }}]">Darabszám *</label>
            <div class="input-group">
                <input type="tel" id="stock-item-count[{{ time() }}]" name="stock-item-count[{{ time() }}]" class="input-count form-control text-right" required>
                <div class="input-group-append">
                    <span class="input-group-text">db</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-auto text-right" style="width: 60px">
        <button class="btn btn-del mb-1 ml-auto btn-remove-stock has-tooltip"
                data-target-id="{{ time() }}" type="button">
            <svg width="32px" height="32px" viewBox="0 0 16 16" class="bi bi-x" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"></path>
            </svg>
        </button>
    </div>
</div>