@php
    /** @var \App\Order $order */
@endphp
@component('mail::message')
# Megrendelés értesítő

Szia {{ $order->lastname }}!

Köszönjük hogy minket választottál!

Üdvözlettel,<br>
Balázs
@endcomponent
