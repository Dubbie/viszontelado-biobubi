@php
    /** @var Order $order */
    use App\Order;
@endphp
<div class="card card-body row flex-row no-gutters mb-4">
    <div class="col-auto">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input ch-order-select"
                   name="ch-order-select[]"
                   id="ch-order-select-{{ $order->inner_id }}"
                   data-order-id="{{ $order->inner_resource_id }}">
            <label class="custom-control-label"
                   for="ch-order-select-{{ $order->inner_id }}"></label>
        </div>
    </div>
    <a href="{{ action('OrderController@show', $order->inner_resource_id) }}"
       class="text-body text-decoration-none col ml-3">
        <div class="row no-gutters">
            <div class="col-11 col-md">
                <p class="mb-1 font-weight-bold">{{ $order->firstname }} {{ $order->lastname }}
                    <small class="d-block text-muted">{{ $order->email }}</small>
                </p>
                <p class="mb-2">{{ $order->getFormattedAddress() }}</p>
            </div>
            <div class="col-12 col-md-5">
                <div class="row align-items-center no-gutters">
                    <div class="col-6 text-lg-right">
                        <p class="font-weight-semibold text-muted mb-0">{{ $order->created_at->format('Y. m. d. H:i') }}</p>
                        <p class="font-weight-semibold mb-0"
                           style="color: {{ $order->status_color }}">{{ $order->status_text }}</p>
                    </div>

                    <div class="col-6 text-right">
                        <p class="h3 font-weight-bold mb-0">{{ number_format($order->total_gross, 0, '.', ' ') }}
                            Ft</p>
                    </div>
                </div>
            </div>
            @if(!$order->isCompleted())
                @if(!$order->isOverdue())
                    <div class="col-12">
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
                    </div>
                @else
                    <div class="col-12">
                        <div style="height: 5px;" class="progress mt-2">
                            <div class="progress-bar bg-danger" style="width: 100%"></div>
                        </div>
                        <p class="mb-0 ml-2">
                            <small class="has-tooltip text-danger-pastel" data-toggle="tooltip"
                                   title="{{ $order->getDeadline()->format('Y.m.d H:i:s') }}">A határidő
                                <b>{{ $order->getDeadline()->shortRelativeDiffForHumans() }}</b>
                                lejárt.</small>
                        </p>
                    </div>
                @endif
            @endif
        </div>
    </a>
    <div class="col-12 mt-3">
        <div class="row no-gutters">
            <div class="col offset-md-8">
                @if(!$order->isCompleted())
                    <form action="{{ action('OrderController@completeOrder') }}" method="POST">
                        @csrf
                        {{-- Rejtett mező a megrendelésnek --}}
                        <input type="hidden" name="order-id" value="{{ $order->inner_resource_id }}">
                        <button type="submit" class="btn btn-success btn-block font-weight-semibold">Teljesítés</button>
                    </form>
                @endif
            </div>
            @if($order->phone)
                <div class="col ml-3">
                    <a href="tel:{{ $order->phone }}" class="btn btn-primary btn-block font-weight-semibold">Hívás</a>
                </div>
            @endif
        </div>
    </div>
</div>
