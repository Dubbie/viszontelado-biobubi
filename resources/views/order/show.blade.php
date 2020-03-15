@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card card-body">
            <div class="row">
                <div class="col-md-4">
                    <p class="mb-0">Rendelésazonosító:</p>
                </div>
                <div class="col-md-8">
                    <p class="mb-0">#{{ $order['order']->innerId }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <p class="mb-0">Vásárló:</p>
                </div>
                <div class="col-md-8">
                    <p class="mb-0">{{ $order['order']->firstname }} {{ $order['order']->lastname }}</p>
                    <p class="mb-0">{{ $order['order']->email }}</p>
                    <p class="mb-0">{{ $order['order']->phone }}</p>
                </div>
            </div>

            <p class="mb-0">Sok sok adat. majd megdumáljuk mi legyen itt...</p>

            <form action="{{ action('OrderController@updateStatus') }}" method="POST" class="mt-4">
                @csrf
                <input type="hidden" name="order-id" value="{{ $order['order']->id }}">
                <div class="form-group">
                    <label for="order-status-href">Megrendelés állapot</label>
                    <select name="order-status-href" id="order-status-href" class="custom-select">
                        <option value=""></option>
                        @foreach($statuses as $orderStatusDescription)
                            <option value="{{ $orderStatusDescription->orderStatus->href }}">{{ $orderStatusDescription->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-success">Változtatás</button>
                </div>
            </form>
        </div>


    </div>

@endsection