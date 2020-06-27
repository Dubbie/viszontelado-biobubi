@php
    /** @var \App\Order $order */
@endphp
@component('mail::message')
# Megrendelés értesítő

Kedves {{ $order->lastname }}!

Csatolva küldöm neked az e-számlát!<br>
Köszönjük, hogy megtiszteltél minket és környezetünket a tudatos döntéseddel! :)

A BioBubihoz jár neked egy **betétdíj kupon, ami 500 Ft kedvezményt ad** a következő 5 literes utántöltő BioBubi-ból! A kosárban használd ezt a kódot:<br>
## BETET500<br>
Itt találod az utántöltőt:<br>
[https://biobubi.hu/utantolto](https://biobubi.hu/utantolto)

A próbacsomag garanciát itt találod:<br>
[https://biobubi.hu/biobubi-garancia](https://biobubi.hu/biobubi-garancia)


**Ne feledd!**<br>
**Az összes tőlünk vásárolt, kiürült flakont visszaváltjuk és újrahasznosítjuk!**

*Barátsággal,*<br>
*Balázs a Semmi Szeméttől*
@endcomponent
