@php
    /** @var Order $order */
    /** @var Worksheet $worksheet */
    use App\Order;use App\Worksheet
@endphp
<div class="row flex-row no-gutters mb-4">
    <div class="col ml-3">
        <div class="row no-gutters">
            <div class="col-11 col-md">
                <a href="{{ action('OrderController@show', $order->inner_resource_id) }}" class="mb-1 font-weight-bold text-decoration-none">{{ $order->firstname }} {{ $order->lastname }}
                    <small class="d-block text-muted">{{ $order->email }}</small>
                </a>
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
            @if($order->isPending())
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
    </div>
    <div class="col-12 mt-3">
        <div class="row no-gutters">
            <div class="col offset-md-8">
                <div class="d-flex justify-content-end">
                    {{-- Hívás gomb --}}
                    @if($order->phone)
                        <a href="tel:{{ $order->phone }}" class="btn btn-icon has-tooltip" data-toggle="tooltip" title="Ügyfél felhívása">
                            <span class="icon icon-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-telephone" viewBox="0 0 16 16">
                                    <path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.568 17.568 0 0 0 4.168 6.608 17.569 17.569 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.678.678 0 0 0-.58-.122l-2.19.547a1.745 1.745 0 0 1-1.657-.459L5.482 8.062a1.745 1.745 0 0 1-.46-1.657l.548-2.19a.678.678 0 0 0-.122-.58L3.654 1.328zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.678.678 0 0 0 .178.643l2.457 2.457a.678.678 0 0 0 .644.178l2.189-.547a1.745 1.745 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.634 18.634 0 0 1-7.01-4.42 18.634 18.634 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877L1.885.511z"/>
                                </svg>
                            </span>
                        </a>
                    @endif

                    {{-- Munkalapra gomb --}}
                    <form action="{{ action('WorksheetController@remove') }}" class="d-inline-block" method="POST">
                        @csrf
                        <input type="hidden" name="ws-id" value="{{ $worksheet->id }}">
                        <button type="submit" class="btn btn-icon has-tooltip" data-toggle="tooltip" title="Eltávolítom a munkalapról">
                            <span class="icon icon-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-clipboard-minus" viewBox="0 0 16 16">
                                  <path fill-rule="evenodd" d="M5.5 9.5A.5.5 0 0 1 6 9h4a.5.5 0 0 1 0 1H6a.5.5 0 0 1-.5-.5z"/>
                                  <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/>
                                  <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/>
                                </svg>
                            </span>
                        </button>
                    </form>

                    {{-- Teljesítés gomb --}}
                    @if(!$order->isCompleted())
                        <form action="{{ action('OrderController@completeOrder') }}" class="form-complete-order d-inline-block ml-2" method="POST">
                            @csrf
                            {{-- Rejtett mező a megrendelésnek --}}
                            <input type="hidden" name="order-id" value="{{ $order->inner_resource_id }}">
                            <button type="submit" class="btn btn-success font-weight-semibold h-100">
                                <span>Teljesítés</span>
                            </button>
                        </form>
                    @else
                        <button type="button" class="disabled btn btn-success ml-2" disabled style="opacity: 0.33">Teljesítés</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
