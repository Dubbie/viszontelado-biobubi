@php
    /** @var \App\Order $order */
@endphp
@component('mail::message')
# Megrendelés értesítő

Szia {{ $order->lastname }}!

Köszönjük hogy minket választottál ISMÉT (Nem próbacsomag levél)!

Üdvözlettel,<br>
Balázs
@endcomponent
