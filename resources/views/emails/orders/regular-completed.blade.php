@php
    /** @var \App\Order $order */
@endphp
@component('mail::message')
# Megrendelés értesítő

Kedves {{ $order->lastname }}!

Csatolva küldöm neked az e-számlát!<br>
Köszönjük, hogy megtiszteltél minket és környezetünket a tudatos döntéseddel! :)

**Ne feledd!**<br>
**Az összes tőlünk vásárolt, kiürült flakont visszaváltjuk és újrahasznosítjuk!**

*Barátsággal,*<br>
*Balázs a Semmi Szeméttől*
@endcomponent
