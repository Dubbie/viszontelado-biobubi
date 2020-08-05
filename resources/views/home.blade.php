@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="font-weight-bold mb-5">Üdvözöljük a <span class="text-success">SemmiSzemét</span> Viszonteladó Portálján!
        </h1>

        @if(!$billingo)
            <div class="alert alert-danger rounded-lg mb-4">
                <div class="row no-gutters">
                    <div class="col-md-auto">
                        <div class="bg-danger-pastel d-flex align-items-center justify-content-center rounded-lg mr-3"
                             style="width: 48px; height: 48px;">
                                <span class="icon text-danger-pastel">
                                    <i class="fas fa-exclamation-circle"></i>
                                </span>
                        </div>
                    </div>
                    <div class="col-md">
                        <p class="mb-0">Jelenleg az automatikus számla kiállítás a fiókjához nincs helyesen
                            beállítva.</p>
                        <p class="mb-0">Kérjük vegye fel a kapcsolatot egy adminisztrátorral, hogy működjön az
                            automatikus számla kiállítás.</p>
                    </div>
                </div>
            </div>
        @endif

        {{--<div class="row no-gutters mb-2">--}}
            {{--<div class="col-md-auto">--}}
                {{--<h4 class="font-weight-bold mb-0">Legutolsó 5 megrendelés</h4>--}}
            {{--</div>--}}
            {{--<div class="col-md">--}}
                {{--<a href="{{ action('OrderController@index') }}" class="btn btn-sm btn-muted">Összes megrendelés megtekintése</a>--}}
            {{--</div>--}}
        {{--</div>--}}
        {{--<div class="card card-body mb-4">--}}
            {{--<table class="table table-responsive-lg table-sm table-borderless mb-0">--}}
                {{--<thead>--}}
                {{--<tr>--}}
                    {{--<th scope="col">--}}
                        {{--<small>Ügyfél</small>--}}
                    {{--</th>--}}
                    {{--<th scope="col">--}}
                        {{--<small>Állapot</small>--}}
                    {{--</th>--}}
                    {{--<th scope="col">--}}
                        {{--<small>Város</small>--}}
                    {{--</th>--}}
                    {{--<th scope="col">--}}
                        {{--<small>Kezdő dátum</small>--}}
                    {{--</th>--}}
                    {{--<th scope="col" class="text-right">--}}
                        {{--<small>Összesen</small>--}}
                    {{--</th>--}}
                    {{--<td></td>--}}
                {{--</tr>--}}
                {{--</thead>--}}
                {{--<tbody>--}}
                {{--@foreach($orders as $order)--}}
                    {{--<tr>--}}
                        {{--<td>--}}
                            {{--<p class="mb-0">{{ $order->firstname }} {{ $order->lastname }}--}}
                                {{--<small class="d-block text-muted">{{ $order->email }}</small>--}}
                            {{--</p>--}}
                        {{--</td>--}}
                        {{--<td class="align-middle">--}}
                            {{--<p class="mb-0" style="color: {{ $order->status_color }}">{{ $order->status_text }}</p>--}}
                        {{--</td>--}}
                        {{--<td class="align-middle"><p class="mb-0">{{ $order->getFormattedAddress() }}</p></td>--}}
                        {{--<td class="align-middle"><p--}}
                                    {{--class="mb-0 text-nowrap">{{ $order->created_at->format('Y. m. d. H:i') }}</p>--}}
                        {{--</td>--}}
                        {{--<td class="text-right align-middle">--}}
                            {{--<p class="mb-0 text-nowrap">{{ number_format($order->total_gross, 0, '.', ' ') }} Ft</p>--}}
                        {{--</td>--}}
                        {{--<td class="align-middle text-right">--}}
                            {{--<a href="{{ action('OrderController@show', ['orderId' => $order->inner_resource_id]) }}"--}}
                               {{--class="btn btn-sm btn-outline-secondary">Részletek</a>--}}
                        {{--</td>--}}
                    {{--</tr>--}}
                {{--@endforeach--}}
                {{--</tbody>--}}
            {{--</table>--}}
        {{--</div>--}}

        <div class="row mb-2">
            <div class="col">
                <h4 class="font-weight-bold mb-0">Legfrissebb hírek</h4>
            </div>
            @if(Auth::user()->admin)
                <div class="col-auto">
                    <a href="{{ action('PostController@create') }}" class="btn btn-teal btn-sm">Bejegyzés hozzáadása</a>
                </div>
            @endif
        </div>
        <div class="row mb-5">
            <div class="col">
                <div class="card card-body">
                    @php /** @var \App\Post $post */ @endphp
                    @foreach($news as $post)
                        <div class="row {{ $news->last() != $post ? 'mb-4' : '' }}">
                            <div class="col-auto" style="width: 128px;">
                                <a href="{{ action('PostController@showPublic', $post) }}">
                                    <img src="{{ $post->getThumbnailUrl() }}" alt="" class="mw-100 d-block rounded-lg border-bottom">
                                </a>
                            </div>
                            <div class="col">
                                <a href="{{ action('PostController@showPublic', $post) }}" class="h4 font-weight-bold lh-100">{{ $post->title }}</a>
                                <p class="mb-2 lh-100">
                                    <small class="text-muted">
                                        <b class="text-dark">{{ $post->author->name }}</b>
                                        <span> - {{ $post->created_at->format('Y.m.d H:i:s') }}</span>
                                    </small>
                                </p>
                                <p class="text-muted mb-0">{{ \Illuminate\Support\Str::limit(strip_tags($post->content), 155) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col">
                <h4 class="font-weight-bold mb-0">Statisztika</h4>
            </div>
            <div class="col text-right">
                <p class="text-muted mb-0">Eddig a hónapban</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="card card-body">
                    <div class="row align-items-center">
                        <div class="col-md-auto">
                            <div class="bg-info-pastel d-flex align-items-center justify-content-center rounded-lg"
                                 style="width: 48px; height: 48px;">
                                <span class="icon text-info-pastel">
                                    <i class="fas fa-clipboard"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-md">
                            <p class="text-muted font-weight-bold text-uppercase mb-0">Bevétel</p>
                            <p class="h2 font-weight-bold mb-0">{{ number_format($income, 0, '.', ' ') }}
                                Ft</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-body">
                    <div class="row align-items-center">
                        <div class="col-md-auto">
                            <div class="bg-danger-pastel d-flex align-items-center justify-content-center rounded-lg"
                                 style="width: 48px; height: 48px;">
                                <span class="icon text-danger-pastel">
                                    <i class="fas fa-wallet"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-md">
                            <p class="text-muted text-uppercase font-weight-bold mb-0">Kiadások</p>
                            <p class="h2 font-weight-bold mb-0">{{ number_format($expense, 0, '.', ' ') }}
                                Ft</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-body">
                    <div class="row align-items-center">
                        <div class="col-md-auto">
                            <div class="bg-success-pastel d-flex align-items-center justify-content-center rounded-lg"
                                 style="width: 48px; height: 48px;">
                                <span class="icon text-success-pastel">
                                    <i class="fas fa-percent"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-md">
                            <p class="text-muted text-uppercase mb-0 font-weight-bold">Profit</p>
                            <p class="h2 font-weight-bold @if($profit > 0) text-success @else text-danger @endif mb-0">{{ number_format($profit, 0, '.', ' ') }}
                                Ft</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
