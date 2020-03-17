@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Bevétel</h1>
            </div>
        </div>

        <div class="card card-body">
            <div class="alert alert-info">
                <p class="mb-0">A grafikonon az adott hónapban teljesített megrendelések értékét látja.</p>
            </div>
            <canvas id="income-chart" width="100" height="50"></canvas>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {

            // Chart.JS
            const ctx = document.getElementById('income-chart');
            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [
                        @foreach($incomeData as $income)
                            new Date("{{ date('Y-m-d 00:00:00', strtotime($income['date'])) }}").toLocaleString(),
                        @endforeach
                    ],
                    datasets: [{
                        label: 'Megrendelés',
                        data: [
                            @foreach($incomeData as $income)
                            {
                                t: new Date("{{ date('Y-m-d 00:00:00', strtotime($income['date'])) }}"),
                                y: {{ $income['total'] }}
                            },
                            @endforeach
                        ],
                    }]
                },
            });

            // Kiszedi az üres mezőket a formból
            $('form').submit(function () {
                var $empty_fields = $(this).find(':input').filter(function () {
                    return $(this).val() === '';
                });
                $empty_fields.prop('disabled', true);
                return true;
            });
        });
    </script>
@endsection