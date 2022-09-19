<p class="h5 font-weight-bold mb-2">Készlet</p>
<p class="text-muted mb-4">A viszonteladó készlet nyilvántartása. A készlet jelenleg <b>nem</b> frissül, ha megrendelés érkezik.</p>
@if($user->stock()->count() > 0)
    @php /** @var \App\Stock $item */ @endphp
    <table class="table table-sm table-borderless table-striped mb-0">
        <thead>
        <tr>
            <th>
                <small class="font-weight-bold">Termék neve</small>
            </th>
            <th class="text-right">
                <small class="font-weight-bold">Készleten</small>
            </th>
        </tr>
        </thead>
        <tbody>
        @foreach($user->stock as $item)
            <tr>
                <td>{{ $item->product->name }} <span class="text-muted">(Cikksz.: <b>{{ $item->sku }}</b>)</span></td>
                <td class="text-right">{{ $item->inventory_on_hand }} db</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <p class="mt-4 mb-0">
{{--        <a href="#addStockToReseller" data-toggle="modal" data-reseller-id="{{ $user->id }}"--}}
{{--           class="btn btn-success btn-toggle-rs-add-modal btn-sm">Készlet hozzáadása</a>--}}
    </p>
@else
    <p class="mb-0 text-muted">Nincs a viszonteladóhoz még készlet nyilvántartás létrehozva.</p>
@endif