@extends('layouts.app')

@section('content')
    @php /** @var \App\Customer $customer */ @endphp
    <div class="container">
        <p class="mb-0">
            <a href="{{ action('CustomerController@index') }}" class="btn-muted font-weight-bold text-decoration-none">
                        <span class="icon icon-sm">
                            <i class="fas fa-arrow-left"></i>
                        </span>
                <span>Vissza az ügyfelekhez</span>
            </a>
        </p>
        <div class="row align-items-baseline">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Ügyfél részletei</h1>
            </div>
        </div>

        <div class="card card-body">
            <div class="row">
                <div class="col-lg-3">
                    <h5 class="font-weight-bold mb-2">Elérhetőség</h5>
                    <p class="text-muted">Az ügyfél első rendelése alapján megadott elérhetőségek.</p>
                </div>
                <div class="col-lg-9">
                    <p class="h3 font-weight-bold mb-0">{{ $customer->getFormattedName() }}</p>
                    <p class="h5 mb-3 text-muted">{{ $customer->email }}</p>

                    {{--Telefonszám--}}
                    <div class="d-flex align-items-center">
                        <span class="icon text-muted mr-2" style="width: 20px; height: 20px;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                              <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                            </svg>
                        </span>
                        <p class="font-weight-bold mb-0">{{ $customer->phone }}</p>
                    </div>

                    {{--Cím--}}
                    <div class="d-flex align-items-center">
                        <span class="icon text-muted mr-2" style="width: 20px; height: 20px;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        <p class="font-weight-bold mb-0">{{ $customer->getFormattedAddress() }}</p>
                    </div>

                    {{-- Mikor rendelt utoljára? --}}
                    <div class="d-flex align-items-center">
                        <span class="icon text-muted mr-2" style="width: 20px; height: 20px;">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        <p class="mb-0">Utoljára <b class="has-tooltip" data-toggle="tooltip" title="{{ $customer->getLastOrderDate()->format('Y.m.d') }}">{{ $customer->getLastOrderTimeAgo() }}</b> rendelt.</p>
                    </div>
                </div>
            </div>

            {{-- Megrendelések --}}
            <div class="row mt-5">
                <div class="col-lg-3">
                    <h5 class="font-weight-bold mb-2">Megrendelései</h5>
                    <p class="text-muted">Az ügyfél összes megrendelése e-mail címe alapján.</p>
                </div>
                <div class="col-lg-9">
                    @if($customer->orders()->count() > 0)
                        @php /** @var \App\Order $localOrder */ @endphp
                        @foreach($customer->orders as $localOrder)
                            <div class="row align-items-center mb-4">
                                <div class="col-12 col-md-7">
                                    <p class="mb-0">
                                        <a href="{{ action('OrderController@show', $localOrder->inner_resource_id) }}">#{{ $localOrder->inner_id }}</a>
                                    </p>
                                    <p class="mb-0">{{ $localOrder->getFormattedAddress() }}</p>
                                    <p class="mb-0"><small>{{ $localOrder->created_at->format('Y.m.d H:i:s') }}</small></p>
                                    @if($localOrder->invoice_id)
                                        <a href="{{ action('OrderController@downloadInvoice', ['orderId' => $localOrder->invoice_id]) }}"
                                           class="btn btn-sm btn-success mt-2">Számla letöltése</a>
                                    @endif
                                </div>
                                <div class="col-12 col-md-5 text-md-right">
                                    <p class="h3 font-weight-semibold mb-0">@money($localOrder->total_gross)<small class="font-weight-bold">Ft</small></p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p>Még nem rendelt az ügyfél.</p>
                    @endif
                </div>
            </div>

            {{--Megjegyzések --}}
            <div class="row mt-5">
                <div class="col-lg-3">
                    <h5 class="font-weight-bold mb-2">Megjegyzések</h5>
                    <p class="text-muted">Az ügyféllel kapcsolatos megjegyzéseket itt tudod rögzíteni.</p>
                </div>
                <div class="col-lg-9">
                    @if($customer->comments()->count() == 0)
                        <p class="h5">Az ügyfélhez még nem fűztek hozzá megjegyzést.</p>
                    @else
                        @php /** @var \App\CustomerComment $comment */ @endphp
                        @foreach($customer->comments as $comment)
                            <div class="order-comment border rounded-lg p-3">
                                <div class="row">
                                    <div class="col">
                                        <p class="text-small mb-0">
                                            <b>{{ $comment->user->name }}</b>
                                            <span class="text-muted"> - </span>
                                            <span>{{ $comment->created_at->format('Y.m.d H:i:s') }}</span>
                                        </p>
                                    </div>
                                    <div class="col-auto">
                                        <div class="d-flex">
                                            <a href="{{ action('CustomerCommentController@edit', $comment) }}"
                                               class="has-tooltip btn-muted btn btn-sm" data-toggle="tooltip"
                                               title="Megjegyzés szerkesztése">
                                                <svg width="16px" height="16px" viewBox="0 0 16 16"
                                                     class="bi bi-pen-fill" fill="currentColor"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd"
                                                          d="M13.498.795l.149-.149a1.207 1.207 0 1 1 1.707 1.708l-.149.148a1.5 1.5 0 0 1-.059 2.059L4.854 14.854a.5.5 0 0 1-.233.131l-4 1a.5.5 0 0 1-.606-.606l1-4a.5.5 0 0 1 .131-.232l9.642-9.642a.5.5 0 0 0-.642.056L6.854 4.854a.5.5 0 1 1-.708-.708L9.44.854A1.5 1.5 0 0 1 11.5.796a1.5 1.5 0 0 1 1.998-.001z"></path>
                                                </svg>
                                            </a>
                                            <form action="{{ action('CustomerCommentController@destroy', $comment) }}"
                                                  method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="has-tooltip btn-muted btn btn-sm btn-del-comment"
                                                        data-toggle="tooltip" title="Megjegyzés törlése">
                                                    <svg width="16px" height="16px" viewBox="0 0 16 16"
                                                         class="bi bi-trash2-fill" fill="currentColor"
                                                         xmlns="http://www.w3.org/2000/svg">
                                                        <path
                                                            d="M2.037 3.225l1.684 10.104A2 2 0 0 0 5.694 15h4.612a2 2 0 0 0 1.973-1.671l1.684-10.104C13.627 4.224 11.085 5 8 5c-3.086 0-5.627-.776-5.963-1.775z"></path>
                                                        <path fill-rule="evenodd"
                                                              d="M12.9 3c-.18-.14-.497-.307-.974-.466C10.967 2.214 9.58 2 8 2s-2.968.215-3.926.534c-.477.16-.795.327-.975.466.18.14.498.307.975.466C5.032 3.786 6.42 4 8 4s2.967-.215 3.926-.534c.477-.16.795-.327.975-.466zM8 5c3.314 0 6-.895 6-2s-2.686-2-6-2-6 .895-6 2 2.686 2 6 2z"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <p class="lead mb-3">{{ $comment->content }}</p>
                            </div>
                        @endforeach
                    @endif

                    <form action="{{ action('CustomerCommentController@store') }}" method="POST" class="mt-4">
                        @csrf
                        <input type="hidden" name="comment-customer-id" value="{{ $customer->id }}">
                        <div class="form-group">
                            <label for="comment-content">Megjegyzés tartalma</label>
                            <textarea name="comment-content" id="comment-content" cols="30" rows="6"
                                      class="form-control" required></textarea>
                        </div>
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-success">Megjegyzés hozzáadása</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- Szűrő --}}
    <script>
        $(function () {
            $('#form-customers-filter').submit(function () {
                var $empty_fields = $(this).find(':input').filter(function () {
                    return $(this).val() === '';
                });
                $empty_fields.prop('disabled', true);
                return true;
            });
        });
    </script>
@endsection
