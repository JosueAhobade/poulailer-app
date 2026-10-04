<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\VaccinationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;


Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'show'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.submit');

});
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth')
        ->name('logout');

    Route::get('/', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/reports', [DailyReportController::class, 'index'])
        ->name('reports.index');

    Route::get('/reports/create', [DailyReportController::class, 'create'])
        ->name('reports.create');

    Route::post('/reports', [DailyReportController::class, 'store'])
        ->name('reports.store');

    Route::get(
        '/reports/{report}/treatments/create',
        [TreatmentController::class, 'create']
    )->name('treatments.create');

    Route::post(
        '/reports/{report}/treatments',
        [TreatmentController::class, 'store']
    )->name('treatments.store');

    Route::get(
        '/reports/{report}/vaccinations/create',
        [VaccinationController::class, 'create']
    )->name('vaccinations.create');

    Route::post(
        '/reports/{report}/vaccinations',
        [VaccinationController::class, 'store']
    )->name('vaccinations.store');

    Route::get(
        '/reports/{report}/consumption/edit',
        [DailyReportController::class, 'editConsumption']
    )->name('reports.consumption.edit');

    Route::put(
        '/reports/{report}/consumption',
        [DailyReportController::class, 'updateConsumption']
    )->name('reports.consumption.update');

});
