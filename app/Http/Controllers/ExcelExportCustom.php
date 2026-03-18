<?php

namespace App\Http\Controllers;

use App\Exports\ReportsCustomerPayment;
use Maatwebsite\Excel\Facades\Excel;

class ExcelExportCustom extends Controller
{
    public function exportReportCustomerPayment(string $ids)
    {
        $export = new ReportsCustomerPayment();
        $export->customerIds = $ids;

        return Excel::download($export, 'reports_customer_payment_' . date('Y-m-d') . '_' . rand(10, 100) . '.xlsx');
    }
}
