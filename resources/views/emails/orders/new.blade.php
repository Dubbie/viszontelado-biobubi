@component('mail::message')
# Megrendelés értesítő

Új megrendelésed érkezett az alábbi adatokkal:

Megrendelő:<br>
**{{ $order['order']->shippingLastname }} {{ $order['order']->shippingFirstname }}**<br>
Cím:<br>
**{{ $order['order']->shippingLastname }} {{ $order['order']->shippingFirstname }}<br>
{{ $order['order']->shippingPostcode }} {{ $order['order']->shippingCity }}, {{ sprintf('%s %s', $order['order']->shippingAddress1, $order['order']->shippingAddress2) }}**<br>
Telefonszám:<br>
**{{ resolve('App\Subesz\PhoneService')->getFormattedPhoneNumber($order['order']->phone) }}**<br>
@if(strlen($order['order']->comment) > 0)
**Megjegyzés:**<br>
**{{ $order['order']->comment }}**
@endif

---

<table>
    <tr>
        <td>Termékek</td>
    </tr>
    @foreach($order['products']->items as $item)
        <tr style="font-weight: bold;">
            <td>{{ $item->name }} (Egységár: {{ $reseller->vat_id == env('AAM_VAT_ID') ? round($item->price * 1.27) : $item->price }} Ft)
            </td>
            <td style="text-align: right">({{ $item->stock1 }} db)</td>
        </tr>
    @endforeach
</table>

---

@foreach($order['totals'] as $total)
@if($total->type == 'TOTAL')
{{ $total->name }}<br>
<b>{{ resolve('App\Subesz\MoneyService')->getFormattedMoney(round(floatval($total->value)) ) }} Ft</b>
@endif
@endforeach

@component('mail::button', ['url' => action('OrderController@show', ['orderId' => $order['order']->id]) ])
Megnézem a megrendelést!
@endcomponent

Üdvözlettel,<br>
{{ config('app.name') }}
@endcomponent
