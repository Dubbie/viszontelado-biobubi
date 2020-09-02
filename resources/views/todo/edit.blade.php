@extends('layouts.app')

@php /** @var \App\OrderTodo $todo */ @endphp
@section('content')
    <div class="container">
        <p class="mb-0">
            @if($todo->order)
                <a href="{{ action('OrderTodoController@show', $todo->order->inner_resource_id) }}" class="btn-muted font-weight-bold text-decoration-none">
                    <span class="icon icon-sm">
                        <i class="fas fa-arrow-left"></i>
                    </span>
                    <span>Vissza a megrendeléshez</span>
                </a>
            @else
                <a href="{{ action('OrderTodoController@index') }}" class="btn-muted font-weight-bold text-decoration-none">
                    <span class="icon icon-sm">
                        <i class="fas fa-arrow-left"></i>
                    </span>
                    <span>Vissza a teendőkhöz</span>
                </a>
            @endif
        </p>
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Teendő szerkesztése</h1>
            </div>
        </div>

        <div class="card card-body mt-4">
            <h3 class="font-weight-bold mb-2">Teendő</h3>
            <form action="{{ action('OrderTodoController@update') }}" method="POST">
                @csrf
                <input type="hidden" name="todo-id" value="{{ $todo->id }}">

                <div class="form-group mt-3">
                    <label for="todo-content">Teendő címe</label>
                    <textarea name="todo-content" id="todo-content" cols="30" rows="2" class="form-control" required>{{ $todo->content }}</textarea>
                </div>

                <div class="form-row">
                    <div class="col">
                        <div class="form-group">
                            <label for="todo-deadline-date">Határidő dátuma</label>
                            <input type="date" name="todo-deadline-date" id="todo-deadline-date" class="form-control" value="{{ $todo->deadline->format('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="todo-deadline-time">Pontos időpont</label>
                            <input type="time" name="todo-deadline-time" id="todo-deadline-time" class="form-control" value="{{ $todo->deadline->format('H:i') }}" required>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-0 mt-4">
                    <button type="submit" class="btn btn-success">Teendő frissítése</button>
                </div>
            </form>
        </div>
    </div>
@endsection