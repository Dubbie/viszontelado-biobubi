@foreach(Auth::user()->worksheet as $wse)
    <x-order :order="$wse->localOrder" type="delivery-notification" :worksheet="$wse"></x-order>
@endforeach