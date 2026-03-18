<table>
    <thead>
        <tr>
            <th><b>FB NAME</b></th>
            <th><b>NAME</b></th>
            <th><b>ORDER DATE</b></th>
            <th><b>ORDER #</b></th>
            <th><b>TRANSACTION #</b></th>
            <th><b>PAYMENT TYPE</b></th>
            <th><b>AMOUNT</b></th>
        </tr>
    </thead>
    <tbody>
    @foreach($customers as $customer)
        <tr>
            <td><b>{{ $customer->nick }}</b></td>
            <td>{{ $customer->name }}</td>
            <td></td>
            <td></td>
        </tr>

        @php
            $transactionsGroupBy = App\Models\Transaction::where('customer_id', $customer->id)->orderBy('customer_id', 'desc')->get()->groupBy('document_id');
        @endphp

        @foreach ($transactionsGroupBy as $key => $transactions)
            @php $document = App\Models\Document::find($key); @endphp
            <tr>
                <td></td>
                <td></td>
                <td>
                    @if ($document?->issued_at)
                        {{ \Carbon\Carbon::parse($document->issued_at)->format('m/d/Y') }}
                    @endif
                </td>
                <td><b>{{ $document?->document_number }}</b></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>

            @php $amountTotal = 0; @endphp
            @foreach ($transactions as $transaction)
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>{{ $transaction->transaction_number }}</td>
                    <td>{{ $transaction->paymentType?->name }}</td>
                    <td>{{ $transaction->amount }}</td>
                </tr>
                @php $amountTotal += $transaction->amount; @endphp
            @endforeach
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td><b>TOTAL</b></td>
                <td><b>{{ $amountTotal }}</b></td>
            </tr>
            <tr>
                <td colspan="7"></td>
            </tr>
        @endforeach
    @endforeach
    </tbody>
</table>
