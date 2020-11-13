@php
    /** @var \App\Order $order */
@endphp
@component('mail::message')
# Megrendelés értesítő

Kedves {{ $order->lastname }}!

Csatolva küldöm neked a számlát!<br>
Köszönöm, hogy nálunk vásároltál!<br>


**További szép napot neked!**
@endcomponent
