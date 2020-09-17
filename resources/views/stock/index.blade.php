@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Készlet</h1>
            </div>
            @if(Auth::user()->admin)
                <div class="col text-right">
                    <a href="{{ action('StockController@create') }}" class="btn btn-teal shadow-sm">Készlet
                        hozzáadása</a>
                </div>
            @endif
        </div>

        <div class="card card-body p-md-5">
            @if($stock->count() > 0)
                @php /** @var \App\Stock $item */ @endphp
                <table class="table table-sm mb-0">
                    <thead>
                    <tr>
                        <th>Termék megnevezés</th>
                        <th>Cikkszám</th>
                        <th class="text-right">Raktáron</th>
                        <th class="text-right">Lefoglalva</th>
                        <th class="text-right">Kiszállítva</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php /** @var \App\Stock $item */ @endphp
                    @foreach($stock as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->sku }}</td>
                            <td class="text-right">{{ $item->inventory_on_hand }} db</td>
                            <td class="text-right">{{ $item->getBookedCount() }} db</td>
                            {{--<td class="text-right">{{ $item->getSoldCount() }} db</td>--}}
                            <td class="text-right">- db</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @else
                <h5 class="font-weight-normal">Még nincs a felhasználódhoz hozzácsatolva készlet információ.</h5>
            @endif

        </div>
    </div>
@endsection
