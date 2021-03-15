@php /** @var \App\MoneyTransfer $transfer */ @endphp
<div {{ $attributes->merge() }}>
    <div class="row align-items-center">
        <div class="col-12 col-md-2">
            <p class="mb-0"><b>{{ $transfer->getId() }}</b></p>
        </div>
        <div class="col-12 col-md-3">
            <p class="mb-0" style="line-height: 1.25">
                <span class="d-block text-truncate">{{ $transfer->reseller->name }}</span>
                <small class="text-muted">{{ $transfer->transfer_orders_count }} megrendelés</small>
            </p>
        </div>
        <div class="col-12 col-md-2 text-md-right">
            <p class="font-weight-semibold mb-0">@money($transfer->amount) Ft</p>
        </div>
        <div class="col-12 col-md-2 text-md-center">
            <p class="mb-0">{{ $transfer->created_at->format('Y.m.d H:i') }}</p>
        </div>
        <div class="col-12 col-md-2 text-md-center">
            <span
                class="font-weight-semibold {{ $transfer->getTextColorClass() }}">{{ $transfer->getStatusText() }}</span>
        </div>
        <div class="col-12 col-md-1 text-md-right">
            <a href="{{ action('MoneyTransferController@show', $transfer) }}"
               class="btn btn-sm px-0 btn-muted d-inline-flex align-items-center has-tooltip" data-toggle="tooltip"
               title="Átutalás részletei">
                <span class="icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                         fill="currentColor"
                         class="bi bi-box-arrow-in-up-right" viewBox="0 0 16 16">
                        <path fill-rule="evenodd"
                              d="M6.364 13.5a.5.5 0 0 0 .5.5H13.5a1.5 1.5 0 0 0 1.5-1.5v-10A1.5 1.5 0 0 0 13.5 1h-10A1.5 1.5 0 0 0 2 2.5v6.636a.5.5 0 1 0 1 0V2.5a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v10a.5.5 0 0 1-.5.5H6.864a.5.5 0 0 0-.5.5z"/>
                        <path fill-rule="evenodd"
                              d="M11 5.5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793l-8.147 8.146a.5.5 0 0 0 .708.708L10 6.707V10.5a.5.5 0 0 0 1 0v-5z"/>
                    </svg>
                </span>
                <span class="d-md-none ml-2">Részletek</span>
            </a>
        </div>
    </div>
</div>