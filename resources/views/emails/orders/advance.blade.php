@php
    /** @var \App\Order $order */
@endphp
@component('mail::message')
    # Megrendelés előleg értesítő

    Kedves {{ $order->lastname }}!

    Csatolva küldöm neked a megrendelésedhez tartozó előlegszámlát!<br>
    Köszönöm, hogy nálunk vásároltál!<br>


    **További szép napot neked!**
@endcomponent
