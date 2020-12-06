$(() => {
    if (window.location.pathname !== '/kozpont/penzugy') {
        return;
    }

    const moment = require('moment');
    const daterangepicker = require('daterangepicker');

    let startDate = null;
    let endDate = null;
    const elRangePicker = document.getElementById('hqf-finance-range');
    const elRangePickerLabel = document.getElementById('hqf-finance-range-label');
    const elView = document.getElementById('cf-mode');
    const elTableContainer = document.getElementById('finance-table-container');
    const elIncomeSum = document.getElementById('hq-income-sum');
    const elExpenseSum = document.getElementById('hq-expense-sum');
    const elTaxSum = document.getElementById('hq-tax-sum');

    function updateFinance() {
        showTextLoaders();

        // Szedjük ki az új adatokat
        fetch('/api/kozpont/penzugy?view-mode=' + elView.options[elView.selectedIndex].value + '&start-date=' + moment(startDate).format('YYYY/MM/DD') + '&end-date=' + moment(endDate).format('YYYY/MM/DD'))
            .then(response => response.json())
            .then(json => {
                $(elTableContainer).html(json.tableHTML);
                elIncomeSum.innerText = Math.round(json.sum['income']).toLocaleString() + ' Ft';
                elExpenseSum.innerText = Math.round(json.sum['expense']).toLocaleString() + ' Ft';
                elTaxSum.innerText = Math.round(json.sum['tax']).toLocaleString() + ' Ft';
            });
    }

    function showTextLoaders() {
        $(elTableContainer).html('<p class="text-muted">Betöltés alatt...</p>');
        elIncomeSum.innerText = '0 Ft';
        elExpenseSum.innerText = '0 Ft';
        elTaxSum.innerText = '0 Ft';
    }

    function updateDatepicker() {
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

            updateRangeLabel();
            updateFinance();
        });

        // Kiadás
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
    }

    function updateRangeLabel() {
        elRangePickerLabel.innerText = moment(startDate).format('YYYY MMMM Do') + ' - ' + moment(endDate).format('YYYY MMMM Do');
    }

    function bindAllElements() {

    }

    function init() {
        updateDatepicker();
        updateRangeLabel();
        bindAllElements();
    }

    init();
});