<table class="table table-sm table-borderless table-striped mb-0">
    <thead>
    <tr>
        <th>Név</th>
        <th>Összeg</th>
        <th>ÁFA</th>
        <th>Létrehozva</th>
        <th>Megjegyzés</th>
    </tr>
    </thead>
    <tbody>
    @foreach($hqFinanceData['data']['incomes']->merge($hqFinanceData['data']['expenses']) as $entry)
        {{--@dump($entry)--}}
        <tr>
            <td>{{ $entry->name }}</td>
            <td>
                @if(get_class($entry) === 'App\Income')
                    <span class="font-weight-bold text-success">+ {{ resolve('App\Subesz\MoneyService')->getFormattedMoney($entry->gross_value) }}
                        Ft</span>
                @else
                    <span class="font-weight-bold text-danger">- {{ resolve('App\Subesz\MoneyService')->getFormattedMoney($entry->gross_value) }}
                        Ft</span>
                @endif
            </td>
            <td>{{ $entry->tax_value > 0 ?  resolve('App\Subesz\MoneyService')->getFormattedMoney($entry->tax_value) . ' Ft' : '-' }}</td>
            <td>{{ \Carbon\Carbon::parse($entry->date)->format('Y.m.d') }}</td>
            <td>{{ $entry->comment ?? '-' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>