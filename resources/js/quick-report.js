$(() => {
    const pageContainer = document.getElementById('quick-report-page');
    if (!pageContainer) {
        return;
    }

    const moment = require('moment');
    const daterangepicker = require('daterangepicker');
    let countMap = null;

    let startDate = null;
    let endDate = null;

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
        // Szedjük ki az új adatokat
        fetch('/api/bevetel?user-id=3&start-date=' + moment(startDate).format('YYYY/MM/DD') + '&end-date=' + moment(endDate).format('YYYY/MM/DD'))
            .then(response => response.json())
            .then(json => {
                removeData(chart);
                countMap = json.count;
                // elIncomeSum.innerText = (json.sum).toLocaleString() + ' Ft';
                // incomeSum = json.sum;
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

    startDate = new Date();
    endDate = new Date(startDate - 1);
    startDate.setDate(1);
    moment.locale('hu');

    fetchIncome();
});