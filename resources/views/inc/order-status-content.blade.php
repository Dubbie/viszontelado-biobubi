{{-- Rejtett mező a megrendelésnek --}}
<input type="hidden" name="order-id" value="{{ $order['order']->id }}">
{{-- Rejtett mező a megrendelés jelenlegi státuszának --}}
<input type="hidden" name="order-status-now" value="{{ $order['order']->orderStatus->href}}">
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
        @foreach($statuses as $orderStatusDescription)
            <option value="{{ $orderStatusDescription->orderStatus->href }}"
                    @if($orderStatusDescription->orderStatus->href == $order['order']->orderStatus->href) selected @endif>
                {{ $orderStatusDescription->name }}
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