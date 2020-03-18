@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Bevétel</h1>
            </div>
        </div>

        <div class="card card-body">
            <div>
                <h2 id="income-range" class="d-inline-block font-weight-bold mb-0" style="cursor: pointer">
                    <span id="income-range-label"></span>
                    <span class="icon">
                        <i class="fas fa-angle-down"></i>
                    </span>
                </h2>
            </div>
            <p id="income-sum" class="font-weight-bold text-muted mb-4">0 Ft</p>
            <canvas id="income-chart" width="100" height="50"></canvas>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {
            let startDate = null;
            let endDate = null;
            const elIncomeRange = document.getElementById('income-range');
            const labelIncomeRange = document.getElementById('income-range-label');
            const elIncomeSum = document.getElementById('income-sum');
            let countMap = null;

            function addData(chart, label, data) {
                chart.data.labels.push(label);
                chart.data.datasets.forEach((dataset) => {
                    dataset.data.push(data);
                });
                chart.update();
            }

            function removeData(chart) {
                chart.data.labels = [];
                chart.data.datasets.forEach((dataset) => {
                    dataset.data = [];
                });
                chart.update();
            }

            function fetchIncome() {
                // Frissítsük a címkét
                labelIncomeRange.innerText = moment(startDate).format('YYYY MMMM Do') + ' - ' + moment(endDate).format('YYYY MMMM Do');

                // Szedjük ki az új adatokat
                fetch('/api/bevetel?start-date=' + moment(startDate).format('YYYY/MM/DD') + '&end-date=' + moment(endDate).format('YYYY/MM/DD'))
                    .then(response => response.json())
                    .then(json => {
                        removeData(chart);

                        countMap = json.count;
                        elIncomeSum.innerText = json.sum.toLocaleString() + ' Ft';
                        for (const data of json.data) {
                            addData(chart, data.x, data);
                        }
                    });
            }

            // Chart.JS
            const ctx = document.getElementById('income-chart');
            const chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Megrendelés',
                        borderColor: "#80b6f4",
                        pointBorderColor: "#80b6f4",
                        pointBackgroundColor: "#80b6f4",
                        pointHoverBackgroundColor: "#80b6f4",
                        pointHoverBorderColor: "#80b6f4",
                        pointBorderWidth: 10,
                        pointHoverRadius: 10,
                        pointHoverBorderWidth: 1,
                        pointRadius: 3,
                        fill: false,
                        borderWidth: 4,
                        data: [],
                    }]
                },
                options: {
                    legend: {
                        display: false,
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                fontColor: "rgba(0,0,0,0.5)",
                                fontStyle: "bold",
                                beginAtZero: true,
                                maxTicksLimit: 5,
                                padding: 20,
                                // Include a dollar sign in the ticks
                                callback: function(value, index, values) {
                                    return value.toLocaleString() + ' Ft';
                                }
                            },
                        }],
                        xAxes: [{
                            gridLines: {
                                zeroLineColor: "transparent"
                            },
                            ticks: {
                                padding: 20,
                                fontColor: "rgba(0,0,0,0.5)",
                                fontStyle: "bold"
                            }
                        }]
                    },
                    tooltips: {
                        custom: function(tooltip) {
                            if (!tooltip) return;
                            tooltip.displayColors = false;
                        },
                        callbacks: {
                            label: function(tooltipItem, data) {
                                const value = tooltipItem.value.toLocaleString() + ' Ft';
                                const found = countMap.filter(obj => {
                                    return obj.date === tooltipItem.xLabel;
                                });

                                if (found.length === 1) {
                                    let multiString = [found[0].count + 'db megrendelés'];
                                    multiString.push(value);
                                    return multiString;
                                } else {
                                    return value;
                                }
                            }
                        }
                    }
                }
            });

            // Kiszedi az üres mezőket a formból
            $('form').submit(function () {
                var $empty_fields = $(this).find(':input').filter(function () {
                    return $(this).val() === '';
                });
                $empty_fields.prop('disabled', true);
                return true;
            });

            // Daterangepicker
            startDate = new Date();
            endDate = new Date();
            endDate.setMonth(endDate.getMonth() + 1);
            endDate.setDate(0);
            startDate.setDate(1);
            moment.locale('hu');

            $(elIncomeRange).daterangepicker({
                "locale": {
                    "format": "MM/DD/YYYY",
                    "separator": " - ",
                    "applyLabel": "Frissítés",
                    "cancelLabel": "Mégse",
                    "fromLabel": "From",
                    "toLabel": "To",
                    "customRangeLabel": "Custom",
                    "weekLabel": "W",
                    "daysOfWeek": [
                        "Va",
                        "Hé",
                        "Ke",
                        "Sze",
                        "Csü",
                        "Pé",
                        "Szo"
                    ],
                    "monthNames": [
                        "Január",
                        "Február",
                        "Március",
                        "Április",
                        "Május",
                        "Június",
                        "Július",
                        "Augusztus",
                        "Szeptember",
                        "Október",
                        "November",
                        "December"
                    ],
                    "firstDay": 1
                },
                "startDate": startDate,
                "endDate": endDate
            }, function(start, end) {
                startDate = start.toDate();
                endDate = end.toDate();

                fetchIncome();
            });

            fetchIncome();
        });
    </script>
@endsection