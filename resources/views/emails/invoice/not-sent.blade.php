@component('mail::message')
# Számla kiküldése sikertelen

Az alábbi megrendeléshez elkészült számlát nem sikerült eljuttatni az ügyfélhez.

Megrendelő:<br>
**{{ $order->shipping_lastname }} {{ $order->shipping_firstname }}**<br>
Cím:<br>
**{{ $order->shipping_postcode }} {{ $order->shipping_city }}, {{ sprintf('%s %s', $order->shipping_address1, $order->shipping_address2) }}**<br>
Telefonszám:<br>
**{{ resolve('App\Subesz\PhoneService')->getFormattedPhoneNumber($order->phone) }}**<br>
E-mail:<br>
**{{ $order->email }}**<br>
@if(strlen($order['order']->comment) > 0)
**Megjegyzés:**<br>
**{{ $order['order']->comment }}**
@endif
@endcomponent
