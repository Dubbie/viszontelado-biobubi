$(function () {
    if (window.location.pathname !== '/riport/aktualis') {
        return;
    }

    const moment = require('moment');
    const daterangepicker = require('daterangepicker');

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

    const elDeliveries = document.getElementById('deliveries-count');

    function fetchExpenses() {
        // Szedjük ki az új adatokat
        fetch('/api/kiadas?start-date=' + moment(startDate).format('YYYY/MM/DD') + '&end-date=' + moment(endDate).format('YYYY/MM/DD'))
            .then(response => response.json())
            .then(json => {
                elExpenseSum.innerText = json.sum.toLocaleString() + ' Ft';
                expenseSum = json.sum;
                renderExpenseData(json.data);
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
                listGroupItem.title = expense.comment;
                listGroupItem.classList.add('has-tooltip');
                listGroupItem.dataset.toggle='tooltip';

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
                amount.innerText = expense['gross_value'].toLocaleString() + ' Ft';

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

        $('.has-tooltip[data-toggle="tooltip"]').tooltip();
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
                let delivered = 0;
                for (const data of json.count) {
                    delivered += data.count;
                }
                elDeliveries.innerText = delivered + ' cím';

                removeData(chart);
                countMap = json.count;
                elIncomeSum.innerText = json.sum.toLocaleString() + ' Ft';
                incomeSum = json.sum;
                for (const data of json.data) {
                    addData(chart, data.x, data);
                }
            });
    }

    // Chart.JS
    const ctx = document.getElementById('income-chart');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Megrendelés',
                backgroundColor: '#52de70',
                borderColor: "#52de70",
                pointBorderColor: "#52de70",
                pointBackgroundColor: "#52de70",
                pointHoverBackgroundColor: "#52de70",
                pointHoverBorderColor: "#52de70",
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
                    gridLines: {
                        // zeroLineColor: "transparent"
                    },
                    ticks: {
                        fontColor: "rgba(0,0,0,0.33)",
                        fontStyle: "bold",
                        beginAtZero: true,
                        maxTicksLimit: 5,
                        padding: 20,
                        // Include a dollar sign in the ticks
                        callback: function(value, index, values) {
                            return intToString(value) + ' Ft';
                        }
                    },
                }],
                xAxes: [{
                    gridLines: {
                        display: false,
                        zeroLineColor: "transparent"
                    },
                    ticks: {
                        padding: 20,
                        fontColor: "rgba(0,0,0,0.33)",
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
    endDate = new Date(startDate - 1);
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