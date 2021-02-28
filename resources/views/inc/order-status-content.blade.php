    {{-- Rejtett mező a megrendelésnek --}}
<input type="hidden" name="order-id" value="{{ $order->inner_resource_id }}">
{{-- Rejtett mező a megrendelés jelenlegi státuszának --}}
<input type="hidden" name="order-status-now" value="{{ $order->status_text }}">
{{-- Állapot --}}
<div class="form-group mb-0">
    <label for="order-status-href">
        <span>Megrendelési állapot</span>
        <span class="icon icon-sm text-muted has-tooltip" data-toggle="tooltip" data-placement="right"
              title="Ezeket az állapotokat a Shoprenter szolgáltatja, a jelenleg aktív állapot automatikusan kijelölésre került">
            <i class="fas fa-info-circle"></i>
        </span>
    </label>
    <select name="order-status-href" id="order-status-href" class="custom-select">
        @foreach($statuses as $os)
            <option value="{{ $os->status_id }}"
                    @if($os->name == $order->status_name) selected @endif>
                {{ $os->name }}
            </option>
        @endforeach
    </select>
</div>
{{-- Értesítő mail a vásárlónak --}}
{{--<div class="form-group mb-0">--}}
    {{--<div class="custom-control custom-checkbox">--}}
        {{--<input type="checkbox" class="custom-control-input" id="notify-customer" name="notify-customer">--}}
        {{--<label class="custom-control-label" for="notify-customer">Értesítő e-mail küldése a vásárlónak</label>--}}
    {{--</div>--}}
{{--</div>--}}
