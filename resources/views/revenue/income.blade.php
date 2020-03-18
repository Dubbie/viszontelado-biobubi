@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Pénzügy</h1>
            </div>
        </div>

        <p id="income-range" class="d-inline-block btn btn-outline-primary mb-4">
            <span id="income-range-label"></span>
            <span class="icon">
                <i class="fas fa-angle-down"></i>
            </span>
        </p>
        <div class="row">
            <div class="col-xl-8">
                <div class="card card-body mb-4">
                    <div>
                        <h5 class="font-weight-bold mb-1">Bevételek a megrendelésekből</h5>
                    </div>
                    <p class="mb-4">
                        <span id="income-sum" class="font-weight-bold text-success h5 mb-4">Betöltés alatt...</span>
                    </p>
                    <canvas id="income-chart" width="100" height="50"></canvas>
                </div>

                <div class="card card-body">
                    <div class="row align-items-baseline">
                        <div class="col-auto">
                            <h2 class="font-weight-bold mb-0">Profit</h2>
                        </div>
                        <div class="col">
                            <h2 id="profit" class="text-muted mb-0">Betöltés alatt...</h2>
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-xl-4">
                <div class="card card-body">
                    <div class="row mb-2">
                        <div class="col">
                            <h5 class="font-weight-bold mb-0">Kiadások</h5>
                            <p id="expense-sum" class="mb-0 text-muted">Betöltés alatt...</p>
                        </div>
                        <div class="col-xl-auto">
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-toggle="modal" data-target="#newExpenseModal">
                                <span>Új kiadás</span>
                            </button>
                        </div>
                    </div>
                    <div id="expense-container"></div>
                </div>
            </div>
        </div>
    </div>

    @include('modal.new-expense')
@endsection

@section('scripts')
    <script>
        $(function () {
            let startDate = null;
            let endDate = null;

            const elRangePicker = document.getElementById('income-range');
            const labelRangePicker = document.getElementById('income-range-label');
            const elIncomeSum = document.getElementById('income-sum');
            let countMap = null;
            let incomeSum = null;

            const elExpenseSum = document.getElementById('expense-sum');
            const elExpenseContainer = document.getElementById('expense-container');
            let expenseSum = null;

            const elProfit = document.getElementById('profit');

            function fetchExpenses() {
                // Szedjük ki az új adatokat
                fetch('/api/kiadas?start-date=' + moment(startDate).format('YYYY/MM/DD') + '&end-date=' + moment(endDate).format('YYYY/MM/DD'))
                    .then(response => response.json())
                    .then(json => {
                        elExpenseSum.innerText = json.sum.toLocaleString() + ' Ft';
                        expenseSum = json.sum;
                        renderExpenseData(json.data);

                        updateProfit();
                    });
            }

            function renderExpenseData(data) {
                while (elExpenseContainer.lastChild) {
                    elExpenseContainer.removeChild(elExpenseContainer.lastChild);
                }

                if (data.length > 0) {
                    const listGroup = document.createElement('div');

                    for (const expense of data) {
                        const listGroupItem = document.createElement('div');

                        const row = document.createElement('div');
                        row.classList.add('row', 'mb-2');
                        row.dataset.expenseId = expense.id;

                        const colLeft = document.createElement('div');
                        colLeft.classList.add('col-md-10');
                        colLeft.style.lineHeight = '1';

                        const name = document.createElement('p');
                        name.classList.add('mb-1', 'font-weight-bold');
                        name.innerText = expense.name;

                        const amount = document.createElement('p');
                        amount.classList.add('mb-1', 'text-muted', 'font-weight-bold');
                        amount.innerText = expense.amount.toLocaleString() + ' Ft';

                        const date = document.createElement('small');
                        date.classList.add('mb-0', 'text-muted');
                        date.innerText = expense.date;

                        colLeft.appendChild(name);
                        colLeft.appendChild(amount);
                        colLeft.appendChild(date);

                        const colRight = document.createElement('div');
                        colRight.classList.add('col-md-2', 'text-right');

                        const btnDel = document.createElement('button');
                        btnDel.type = 'button';
                        btnDel.classList.add('btn', 'btn-sm', 'btn-muted');
                        btnDel.addEventListener('click', (e) => {
                            if (confirm('Biztosan ki szeretné törölni ezt a kiadást? (' + expense.name + ')\nEz a folyamat nem visszafordítható.')) {
                                deleteExpense(expense.id);
                            }
                        });

                        const icon = document.createElement('i');
                        icon.classList.add('fas', 'fa-times-circle');

                        btnDel.appendChild(icon);

                        colRight.appendChild(btnDel);

                        row.appendChild(colLeft);
                        row.appendChild(colRight);

                        listGroupItem.appendChild(row);

                        listGroup.appendChild(listGroupItem);
                    }

                    elExpenseContainer.appendChild(listGroup);
                } else {
                    const label = document.createElement('p');
                    label.innerText = "Nincsenek a megadott időintervallumra eső kiadások";
                    label.classList.add('mb-0');
                    elExpenseContainer.appendChild(label);
                }
            }

            function updateProfit() {
                if (incomeSum != null && expenseSum != null) {
                    const profit = incomeSum - expenseSum;
                    elProfit.classList.remove('text-muted', 'text-danger', 'text-succes');
                    elProfit.innerText = profit.toLocaleString() + ' Ft';
                    elProfit.classList.add('text-success');
                    if (profit < 0) {
                        elProfit.classList.add('text-danger');
                    }
                }
            }

            function deleteExpense(expenseId) {
                const row = $('.row[data-expense-id="' + expenseId + '"]')[0];

                fetch('/api/kiadas/' + expenseId + '/torles')
                    .then(response => response.json())
                    .then(json => {
                        fetchExpenses();
                    });
            }

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
                labelRangePicker.innerText = moment(startDate).format('YYYY MMMM Do') + ' - ' + moment(endDate).format('YYYY MMMM Do');

                // Szedjük ki az új adatokat
                fetch('/api/bevetel?start-date=' + moment(startDate).format('YYYY/MM/DD') + '&end-date=' + moment(endDate).format('YYYY/MM/DD'))
                    .then(response => response.json())
                    .then(json => {
                        removeData(chart);
                        countMap = json.count;
                        elIncomeSum.innerText = json.sum.toLocaleString() + ' Ft';
                        incomeSum = json.sum;
                        for (const data of json.data) {
                            addData(chart, data.x, data);
                        }
                        updateProfit();
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
                        pointBorderWidth: 7,
                        pointHoverRadius: 7,
                        pointHoverBorderWidth: 1,
                        pointRadius: 2,
                        fill: false,
                        borderWidth: 3,
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
                                fontStyle: "bold",
                                maxTicksLimit: 10,
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

            $(elRangePicker).daterangepicker({
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
                fetchExpenses();
            });

            // Új kiadáshoz
            $('#e-date').daterangepicker({
                "locale": {
                    "format": "YYYY/MM/DD",
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
                "startDate": moment(),
                "singleDatePicker": true,
            });

            fetchIncome();
            fetchExpenses();
        });
    </script>
@endsection