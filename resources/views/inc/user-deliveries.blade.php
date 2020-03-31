@if(count($deliveries) > 0)
    <ul class="list-unstyled mb-0" style="overflow-y: scroll; overflow-x: hidden; height: 165px;">
        @php $deliverySum = 0 @endphp
        @foreach($deliveries as $delivery)
            @php $deliverySum += $delivery->order->total_gross @endphp
            <li>
                {{--<p class="mb-0"><span>{{ $delivery->order->getFormattedAddress() }}</span></p>--}}
                <div class="row align-items-baseline">
                    <div class="col">
                        <a href="{{ action('OrderController@show', $delivery->order->inner_resource_id) }}"
                           class="font-weight-bold">{{ $delivery->order->firstname }} {{ $delivery->order->lastname }}</a>
                        <small class="d-block text-white-50">{{ $delivery->delivered_at->translatedFormat('Y M d, H:i') }}</small>
                    </div>
                    <div class="col-auto">
                        <p class="mb-0 font-weight-bold pr-2">{{ number_format($delivery->order->total_gross, 0, '.', ' ') }}
                            Ft</p>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>

    @if(count($benjiMoney) > 0)
        <p class="h5 font-weight-bold mt-4 mb-2">Benjitől kapott összegek</p>
        <ul class="list-unstyled mb-0" style="overflow-y: scroll; overflow-x: hidden; height: 90px;">
            @php $benjiSum = 0 @endphp
            @foreach($benjiMoney as $benji)
                @php $benjiSum += $benji->amount @endphp
                <li>
                    <div class="row align-items-baseline">
                        <div class="col">
                            <small class="d-block text-white-50">{{ $benji->given_at->translatedFormat('Y M d, H:i') }}</small>
                        </div>
                        <div class="col-auto">
                            <p class="mb-0 font-weight-bold pr-2">{{ number_format($benji->amount, 0, '.', ' ') }}
                                Ft</p>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    <div class="row align-items-baseline mt-4">
        <div class="col-auto">
            <small class="text-white-50">Megrendelések összesen:</small>
        </div>
        <div class="col text-right">
            <p id="deliveries-sum" class="mb-0 font-weight-bold" data-sum="{{ $deliverySum }}">{{ number_format($deliverySum, 0, '.', ' ') }} Ft</p>
        </div>
    </div>
    @if(count($benjiMoney) > 0)
        <div class="row align-items-baseline">
            <div class="col-auto">
                <small class="text-white-50">Benji által adva:</small>
            </div>
            <div class="col text-right">
                <p id="deliveries-sum" class="mb-0 font-weight-bold"
                   data-sum="{{ $benjiSum }}">{{ number_format($benjiSum, 0, '.', ' ') }} Ft</p>
            </div>
        </div>

        <div class="row align-items-baseline">
            <div class="col-auto">
                <p class="text-white-50 mb-0">Tartozás:</p>
            </div>
            <div class="col text-right">
                <p id="deliveries-sum"
                   class="h5 mb-0 font-weight-bold @if(($deliverySum - $benjiSum) > 0) text-danger-pastel @else text-success @endif">
                    <span>{{ number_format(($deliverySum - $benjiSum), 0, '.', ' ') }} Ft</span>
                </p>
            </div>
        </div>
    @else
        <p class="text-warning">Benji még nem adott tartozást</p>
    @endif

    <form id="form-benji-money" action="{{ action('BenjiMoneyController@store') }}" method="POST" class="mt-2">
        @csrf
        <div class="form-row">
            <div class="col">
                <div class="form-group mb-0">
                    <label for="benji-money-amount" class="sr-only">Levonandó összeg</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="benji-money-amount" name="benji-money-amount"
                               class="form-control form-control-sm text-right"
                               placeholder="Levonandó összeg..." required>
                        <div class="input-group-append">
                            <span class="input-group-text">Ft</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-sm btn-success">Mentés</button>
                </div>
            </div>
        </div>
    </form>
@else
    <p class="text-white-50 mb-0">Benji még egy megrendelést sem teljesített ki a portálon keresztül.</p>
@endif