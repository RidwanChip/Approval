<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/report/approval', [ReportController::class, 'generatePDF'])->name('report.approval');
