@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="font-weight-bold mb-4">Kiadások</h1>
            </div>
        </div>

        <div class="card card-body">
            <div>
                <h2 id="expense-range" class="d-inline-block font-weight-bold mb-0" style="cursor: pointer">
                    <span id="expense-range-label"></span>
                    <span class="icon">
                        <i class="fas fa-angle-down"></i>
                    </span>
                </h2>
            </div>
            <p id="expense-sum" class="font-weight-bold text-muted mb-4">0 Ft</p>
            <div id="expenses-container"></div>
            <div>
                <button type="button" class="btn btn-outline-secondary"
                        data-toggle="modal" data-target="#newExpenseModal">
                    <span class="icon">
                        <i class="fas fa-plus"></i>
                    </span>
                    <span>Új kiadás felvétele</span>
                </button>
            </div>
        </div>
    </div>

    @include('modal.new-expense')
@endsection

@section('scripts')
    <script>
        $(() => {
            // Magyarosítás
            moment.locale('hu');

            let startDate = moment().startOf('month');
            let endDate = moment().endOf('month');
            const elExpenseRange = document.getElementById('expense-range');
            const labelExpenseRange = document.getElementById('expense-range-label');
            const elExpenseSum = document.getElementById('expense-sum');
            const elExpenseContainer = document.getElementById('expenses-container');

            function fetchExpenses() {
                // Frissítsük a címkét
                labelExpenseRange.innerText = moment(startDate).format('YYYY MMMM Do') + ' - ' + moment(endDate).format('YYYY MMMM Do');

                // Szedjük ki az új adatokat
                fetch('/api/kiadas?start-date=' + moment(startDate).format('YYYY/MM/DD') + '&end-date=' + moment(endDate).format('YYYY/MM/DD'))
                    .then(response => response.json())
                    .then(json => {
                        elExpenseSum.innerText = json.sum.toLocaleString() + ' Ft';
                        renderExpenseData(json.data);
                    });
            }

            function renderExpenseData(data) {
                while (elExpenseContainer.lastChild) {
                    elExpenseContainer.removeChild(elExpenseContainer.lastChild);
                }

                const listGroup = document.createElement('div');
                listGroup.classList.add('list-group', 'mb-4');

                for (const expense of data) {
                    const listGroupItem = document.createElement('div');
                    listGroupItem.classList.add('list-group-item');

                    const row = document.createElement('div');
                    row.classList.add('row');

                    const colLeft = document.createElement('div');
                    colLeft.classList.add('col-md-7');

                    const name = document.createElement('p');
                    name.classList.add('mb-0', 'font-weight-bold', 'h3', 'text-success');
                    name.innerText = expense.name;

                    const date = document.createElement('p');
                    date.classList.add('mb-0', 'text-muted');
                    date.innerText = expense.date;

                    colLeft.appendChild(name);
                    colLeft.appendChild(date);

                    const colRight = document.createElement('div');
                    colRight.classList.add('col-md-5', 'text-right');

                    const amount = document.createElement('p');
                    amount.classList.add('mb-0', 'h3');
                    amount.innerText = expense.amount.toLocaleString() + ' Ft';

                    colRight.appendChild(amount);

                    row.appendChild(colLeft);
                    row.appendChild(colRight);

                    listGroupItem.appendChild(row);

                    listGroup.appendChild(listGroupItem);
                }

                elExpenseContainer.appendChild(listGroup);
            }

            // Kereső
            $(elExpenseRange).daterangepicker({
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
                startDate = start;
                endDate = end;

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

            fetchExpenses();
        });
    </script>
@endsection