@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Felhasználók</h1>
            </div>
            <div class="col text-right">
                <a href="{{ action('UserController@create') }}" class="btn btn-teal shadow-sm">Új felhasználó</a>
            </div>
        </div>
        <div class="card card-body">
            <table class="table table-sm table-responsive-md table-borderless mb-0">
                <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Név</th>
                    <th scope="col">E-mail</th>
                    <th scope="col">AAM</th>
                    <th scope="col">Ir. számok</th>
                    <th scope="col">Kiszállítva</th>
                    <th scope="col"></th>
                </tr>
                </thead>
                <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }} @if($user->admin) <span class="badge badge-success">Admin</span> @endif</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->vat_id == env('AAM_VAT_ID') ? 'Igen' : 'Nem' }}</td>
                        <td>{{ $user->zips_count }} db</td>
                        <td>{{ $user->deliveries_count }} db</td>
                        <td class="text-right">
                            <a href="{{ action('UserController@show', $user) }}" class="btn btn-sm btn-outline-secondary">Részletek</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @include('modal.user-details')
@endsection

@section('scripts')
    <script>
        $( () => {
            const modal = document.getElementById('userDetailsModal');
            const userDetails = modal.querySelector('#user-details');
            const loading = modal.querySelector('.modal-loader');

            // Jármű részleteinek betöltése
            $(document).on('click', '.btn-user-details', (e) => {
                const userId = e.currentTarget.dataset.userId;
                $(loading).show();
                $(userDetails).hide();
                fetch('/felhasznalok/' + userId).then(response => response.text()).then(html => {
                    userDetails.innerHTML = html;
                    $(loading).hide();
                    $(userDetails).show();
                    $('.has-tooltip').tooltip();
                });
            });
        });
    </script>
@endsection