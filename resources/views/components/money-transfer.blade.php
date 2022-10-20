@php /** @var \App\MoneyTransfer $transfer */ @endphp
<div {{ $attributes->merge() }}>
    <div class="row align-items-center no-gutters">
        <div class="col-12 col-md-2">
            <div class="d-flex">
                <div class="custom-control custom-checkbox mr-2">
                    <input type="checkbox" class="custom-control-input ch-transfer-select mr-2"
                           name="ch-transfer-select[]"
                           id="ch-transfer-select-{{ $transfer->id }}"
                           data-transfer-id="{{ $transfer->id }}">
                    <label class="custom-control-label"
                           for="ch-transfer-select-{{ $transfer->id }}"></label>
                </div>
                <p class="mb-0"><b>{{ $transfer->getId() }}</b></p>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <p class="mb-0" style="line-height: 1.25">
                <span class="d-block text-truncate">{{ $transfer->reseller->name }} @if( !$transfer->reseller->billable()) <small><a class="text-danger-pastel"
                            href="{{ action('UserController@edit', ['userId' => $transfer->reseller->id]) }}">Hiányzó adatok</a></small> @endif</span>
                <small class="text-muted">{{ $transfer->transfer_orders_count }} megrendelés</small>
            </p>
        </div>
        <div class="col-12 col-md-2 text-md-right">
            <p class="font-weight-semibold mb-0">@money($transfer->amount) Ft</p>
        </div>
        <div class="col-12 col-md-1 text-md-right">
            @if($transfer->hasCommissionFee())
                <p class="font-weight-semibold mb-0">@money($transfer->getCommissionFee()) Ft</p>
            @else
                <p class="font-weight-semibold mb-0 text-muted">-</p>
            @endif
        </div>
        <div class="col-12 col-md-2 text-md-center">
            <p class="mb-0">{{ $transfer->created_at->format('Y.m.d') }}</p>
        </div>
        <div class="col-12 col-md-1 text-md-center">
            <small
                class="font-weight-bold {{ $transfer->getTextColorClass() }}">{{ $transfer->getStatusText() }}</small>
        </div>
        <div class="col-12 col-md-1 text-md-right">
            <div class="d-flex justify-content-md-end">
                {{-- Részletek gomb --}}
                <a href="{{ action('MoneyTransferController@show', $transfer) }}"
                   class="btn btn-sm px-0 btn-muted d-inline-flex align-items-center has-tooltip"
                   data-toggle="tooltip"
                   title="Átutalás részletei">
                    <span class="icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                             fill="currentColor"
                             class="bi bi-box-arrow-in-up-right " viewBox="0 0 16 16">
                            <path fill-rule="evenodd"
                                  d="M6.364 13.5a.5.5 0 0 0 .5.5H13.5a1.5 1.5 0 0 0 1.5-1.5v-10A1.5 1.5 0 0 0 13.5 1h-10A1.5 1.5 0 0 0 2 2.5v6.636a.5.5 0 1 0 1 0V2.5a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v10a.5.5 0 0 1-.5.5H6.864a.5.5 0 0 0-.5.5z"/>
                            <path fill-rule="evenodd"
                                  d="M11 5.5a.5.5 0 0 0-.5-.5h-5a.5.5 0 0 0 0 1h3.793l-8.147 8.146a.5.5 0 0 0 .708.708L10 6.707V10.5a.5.5 0 0 0 1 0v-5z"/>
                        </svg>
                    </span>
                    <span class="d-md-none ml-2">Részletek</span>
                </a>

                @if(Auth::user()->admin)
                    {{-- Törlés gomb --}}
                    <form class="form-delete-transfer"
                          action="{{ action('MoneyTransferController@destroy', $transfer) }}"
                          method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="btn btn-sm px-0 btn-muted d-inline-flex align-items-center has-tooltip ml-3 ml-md-0"
                                data-toggle="tooltip"
                                title="Átutalás törlése">
                            <span class="icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                     class="bi bi-trash" viewBox="0 0 16 16">
                                    <path
                                        d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/>
                                    <path fill-rule="evenodd"
                                          d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4L4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/>
                                </svg>
                            </span>
                            <span class="d-md-none ml-2">Törlés</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>