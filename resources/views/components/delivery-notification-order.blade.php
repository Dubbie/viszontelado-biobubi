@php
    /** @var Order $order */
    /** @var Worksheet $worksheet */
@endphp
<div class="dno-row row flex-row no-gutters d-flex mb-3" data-ws-id="{{ $worksheet->id }}">
    <div class="col-1">
        <div class="custom-control custom-checkbox text-center">
            <input type="checkbox" class="custom-control-input ch-notification-order-select"
                   name="ch-notification-order-select[]"
                   id="ch-notification-order-select-{{ $order->inner_id }}"
                   data-order-id="{{ $order->inner_resource_id }}" value="{{ $order->inner_resource_id }}">
            <label class="custom-control-label"
                   for="ch-notification-order-select-{{ $order->inner_id }}"></label>
        </div>
    </div>
    <div class="col-9 col-md">
        <div class="row">
            <div class="col-8">
                <a href="{{ action('OrderController@show', $order->inner_resource_id) }}"
                   class="mb-1 font-weight-bold text-decoration-none">
                    <span class="dno-name">{{ $order->getFormattedName() }}</span>
                    <small class="d-block text-muted dno-email">{{ $order->email }}</small>
                </a>
                <p class="mb-2 dno-address">{{ $order->getFormattedAddress() }}</p>
            </div>
            <div class="col-4 text-right">
                @if($order->delivery_notification_sent)
                    <p class="text-success-pastel">Értesítve a kiszállításról</p>
                @else
                    <p class="text-muted">Nem értesült szállításról még</p>
                @endif
            </div>
        </div>

        <p class="font-weight-semibold text-muted mb-0">{{ $order->created_at->format('Y. m. d. H:i') }}</p>
        <p class="font-weight-semibold mb-0"
           style="color: {{ $order->status_color }}">{{ $order->status_text }}</p>

        <p class="h5 font-weight-bold mb-0">{{ number_format($order->total_gross, 0, '.', ' ') }}
            Ft</p>

        @if($order->isPending())
            @if(!$order->isOverdue())
                <div style="height: 5px;" class="progress mt-2">
                    <div class="progress-bar"
                         style="width: {{ $order->getProgress() }}%; background-color: {{ $order->status_color }}"></div>
                </div>

                <div class="row no-gutters">
                    <div class="col">
                        <p class="mb-0">
                            <small class="has-tooltip" data-toggle="tooltip"
                                   title="{{ $order->getDeadline()->format('Y.m.d H:i:s') }}">Hátralévő
                                idő:
                                <b>{{ $order->getDeadline()->shortAbsoluteDiffForHumans() }}</b></small>
                        </p>
                    </div>
                    <div class="col text-right">
                        <p class="mb-0 text-muted">
                            <small>{{ $order->getDeadline()->format('Y.m.d H:i:s') }}</small></p>
                    </div>
                </div>
            @else
                <div style="height: 5px;" class="progress mt-2">
                    <div class="progress-bar bg-danger" style="width: 100%"></div>
                </div>
                <p class="mb-0 ml-2">
                    <small class="has-tooltip text-danger-pastel" data-toggle="tooltip"
                           title="{{ $order->getDeadline()->format('Y.m.d H:i:s') }}">A határidő
                        <b>{{ $order->getDeadline()->shortRelativeDiffForHumans() }}</b>
                        lejárt.</small>
                </p>
            @endif
        @endif
    </div>
</div>
