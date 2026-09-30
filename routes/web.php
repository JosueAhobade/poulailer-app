<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DailyReportController;

Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/reports', [DailyReportController::class, 'index'])
    ->name('reports.index');

Route::get('/reports/create', [DailyReportController::class, 'create'])
    ->name('reports.create');

Route::post('/reports', [DailyReportController::class, 'store'])
    ->name('reports.store');