@extends('layouts.app')

@section('content')
    <div class="container">
        <p class="mb-0">
            <a href="{{ action('OrderTodoController@index') }}" class="btn-muted font-weight-bold text-decoration-none">
                <span class="icon icon-sm">
                    <i class="fas fa-arrow-left"></i>
                </span>
                <span>Vissza a teendőkhöz</span>
            </a>
        </p>
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Teendő hozzáadása</h1>
            </div>
        </div>

        <div class="card card-body">
            <h5 class="font-weight-bold mb-4">Teendő adatai</h5>
            <form action="{{ action('OrderTodoController@store') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="todo-content">Teendő</label>
                    <textarea name="todo-content" id="todo-content" cols="30" rows="1" class="form-control" required></textarea>
                </div>

                <div class="form-row">
                    <div class="col">
                        <div class="form-group">
                            <label for="todo-deadline-date">Határidő dátuma</label>
                            <input type="date" name="todo-deadline-date" id="todo-deadline-date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label for="todo-deadline-time">Pontos időpont</label>
                            <input type="time" name="todo-deadline-time" id="todo-deadline-time" class="form-control" value="12:00" required>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-sm btn-success">Teendő hozzáadása</button>
                </div>
            </form>
        </div>
    </div>
@endsection