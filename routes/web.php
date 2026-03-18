<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcelExportCustom;

Route::get('/', function () {
    return view('welcome');
});

Route::get('reports-customer-payment/{ids}', [ExcelExportCustom::class, 'exportReportCustomerPayment'])
    ->name('reports.customer-payment');
