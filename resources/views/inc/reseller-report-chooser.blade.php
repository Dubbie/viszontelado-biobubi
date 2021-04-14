<div id="report-monthly-view" class="d-none">
    @include('inc.reseller-monthly-reports')
</div>
<div id="report-yearly-view" class="d-none">
    @include('inc.reseller-yearly-reports')
</div>

@section('scripts')
    @parent
    <script>

        $(() => {
            // Riport választó
            let reportType = `{{$reportView}}`;
            const monthlyCont = $("#report-monthly-view");
            const monthlyBtn = $("#btn-monthly-view");
            const yearlyCont = $("#report-yearly-view");
            const yearlyBtn = $("#btn-yearly-view");

            monthlyBtn.on('click', showMonthly);
            yearlyBtn.on('click', showYearly);

            function showMonthly() {
                monthlyCont[0].classList.remove('d-none');
                yearlyCont[0].classList.add('d-none');
            }

            function showYearly() {
                yearlyCont[0].classList.remove('d-none');
                monthlyCont[0].classList.add('d-none');
            }

            if (reportType == 'monthly-reports')
                showMonthly();
            if (reportType == 'yearly-reports')
                showYearly();

            //riport formok
            $('#date').on('change', e => {
                $(e.currentTarget).closest('form').submit();
            });
            $('#year').on('change', e => {
                $(e.currentTarget).closest('form').submit();
            });
        });
    </script>
@stop
