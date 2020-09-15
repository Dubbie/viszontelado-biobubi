@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Központi készlet</h1>
            </div>
            @if(Auth::user()->admin)
                <div class="col text-right">
                    <a href="{{ action('StockController@create') }}" class="btn btn-teal shadow-sm">Készlet
                        hozzáadása</a>
                </div>
            @endif
        </div>

        <div class="card card-body p-md-5">
            @php /** @var \App\User $user */ @endphp
            @foreach($users as $user)
                <div class="row @if($users->last() != $user) mb-4 @endif">
                    <div class="col-md-3">
                        <p class="mb-0 h4 font-weight-bold">{{ $user->name }}</p>
                        <p class="mb-0">
                            <small class="text-muted">{{ $user->email }}</small>
                        </p>
                    </div>
                    <div class="col-md-9">
                        <div class="bg-muted p-3 rounded-lg">
                            @if($user->stock()->count() > 0)
                                @php /** @var \App\Stock $item */ @endphp
                                <table class="table table-sm table-borderless mb-0">
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
                                    @foreach($user->stock as $item)
                                        <tr>
                                            <td>{{ $item->product->name }}</td>
                                            <td>{{ $item->sku }}</td>
                                            <td class="text-right">{{ $item->inventory_on_hand }} db</td>
                                            <td class="text-right">{{ $item->getBookedCount() }} db</td>
                                            <td class="text-right">{{ $item->getSoldCount() }} db</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>

                                <a href="{{ action('StockController@edit', $user) }}" class="btn btn-success btn-sm mt-4">Viszonteladó készletének szerkesztése</a>
                            @else
                                <p class="mb-0 text-muted">Nincs a viszonteladóhoz még készlet nyilvántartás létrehozva.</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
