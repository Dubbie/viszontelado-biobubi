@extends('layouts.pdf')

@section('content')
    <script type="text/php">
        if ( isset($pdf) ) {
            // OLD
            // $font = Font_Metrics::get_font("helvetica", "bold");
            // $pdf->page_text(72, 18, "{PAGE_NUM} of {PAGE_COUNT}", $font, 6, array(255,0,0));
            // v.0.7.0 and greater
            $x = 540;
            $y = 820;
            $text = "{PAGE_NUM} / {PAGE_COUNT} oldal";
            $font = $fontMetrics->get_font("deja vu", "bold");
            $size = 9;
            $color = array(0,0,0);
            $word_space = 0.0;  //  default
            $char_space = 0.0;  //  default
            $angle = 0.0;   //  default
            $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
        }
    </script>
    <h1 style="margin-top: 0;"><b>BioBubi</b> Szállítólevél</h1>
    @foreach($data as $order)
        <table width="100%" cellpadding="5px" cellspacing="0">
            <tr>
                <td width="33.33%" style="border: 1px solid #ddd">
                    <p><b>Számlázási cím:</b><br>
                        {{ $order->paymentLastname }} {{ $order->paymentFirstname }}<br>
                        {{ $order->paymentPostcode }} {{ $order->paymentCity }}<br>
                        {{ sprintf('%s %s', $order->paymentAddress1, $order->paymentAddress2) }}<br>
                        {{ $order->email }}<br>
                        {{ resolve('App\Subesz\PhoneService')->getFormattedPhoneNumber($order->phone) }}</p>
                </td>
                <td width="33.33%" style="border: 1px solid #ddd">
                    <p>
                        <b>Rendelés azonosító: </b><br>{{ $order->innerId }}<br>
                        <b>Szállítási cím:</b><br>
                        {{ $order->shippingLastname }} {{ $order->shippingFirstname }}<br>
                        {{ $order->shippingPostcode }} {{ $order->shippingCity }}<br>
                        {{ sprintf('%s %s', $order->shippingAddress1, $order->shippingAddress2) }}<br>
                    </p>
                    <p style="margin-top: 10px"><b>@if($order->statusData->name == 'BK. Függőben lévő') Bankkártyával fizetve @endif</b></p>
                    @if(strlen($order->comment) > 0)
                        <p>
                            <small><b>Megjegyzés: </b></small>
                            <br>
                            <small>{{ $order->comment }}</small>
                        </p>
                    @endif
                </td>
                <td width="33.33%" style="border: 1px solid #ddd">
                    <p><b>Felvételi dátum:</b><br>
                        {{ date('Y. m. d.') }}<br>
                        @foreach($order->orderTotals as $total)
                            @if(in_array($total->type, ['SHIPPING', 'TOTAL', 'COUPON']))
                                <b>{{ $total->name }}</b><br>{{ resolve('App\Subesz\MoneyService')->getFormattedMoney(round(floatval($total->value)) ) }} Ft @if(last($order->orderTotals) != $total) <br> @endif
                            @endif
                        @endforeach
                    </p>
                </td>
            </tr>
        </table>
        <table style="margin-bottom: 2rem;">
            <tr>
                <td><b>Termékek</b></td>
            </tr>
            @foreach($order->orderProducts as $item)
                <tr>
                    <td>{{ $item->name }} (Egységár: {{ resolve('App\Subesz\MoneyService')->getFormattedMoney(round($item->price * 1.27)) }} Ft)
                    </td>
                    <td>({{ $item->stock1 }} db)</td>
                </tr>
            @endforeach
        </table>
    @endforeach

    <table>
        <thead>
        <tr>
            <th align="left">Termék</th>
        </tr>
        </thead>
        <tbody>
        @foreach($sum['items'] as $item)
            <tr>
                <td><b>{{ $item['count'] }} db</b> {{ $item['name'] }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="2">Összes szállítási költség: {{ resolve('App\Subesz\MoneyService')->getFormattedMoney(round($sum['shipping'])) }} Ft</td>
        </tr>
        <tr>
            <td colspan="2">Összes bruttó bevétel: {{ resolve('App\Subesz\MoneyService')->getFormattedMoney(round($sum['income'])) }} Ft</td>
        </tr>
        </tbody>
    </table>
@endsection
