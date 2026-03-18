<?php

namespace App\Exports;

use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ReportsCustomerPayment implements FromView
{
    public string $customerIds = '';

    public function view(): View
    {
        $customerIds = array_filter(explode('-', $this->customerIds));

        return view('exports.reports-customer-payment', [
            'customers' => Customer::query()
                ->whereIn('id', $customerIds)
                ->orderByDesc('id')
                ->get(),
        ]);
    }
}
