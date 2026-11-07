<?php

use App\Http\Controllers\AccountingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DailyReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EggCollectionController;
use App\Http\Controllers\EggSaleController;
use App\Http\Controllers\InputController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\VaccinationController;
use Illuminate\Support\Facades\Route;

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

    Route::get('/reports/{report}', [DailyReportController::class, 'show'])
        ->name('reports.show');

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

    // -----------------------------------------
    // INTRANTS (provende, vaccins, médicaments)
    // -----------------------------------------

    Route::resource('inputs', InputController::class);

    Route::get(
        '/inputs/{input}/movements/create',
        [StockMovementController::class, 'create']
    )->name('inputs.movements.create');

    Route::post(
        '/inputs/{input}/movements',
        [StockMovementController::class, 'store']
    )->name('inputs.movements.store');

    Route::delete(
        '/movements/{movement}',
        [StockMovementController::class, 'destroy']
    )->name('movements.destroy');

    // -----------------------------------------
    // ŒUFS : ramassages et ventes
    // -----------------------------------------

    Route::resource('eggs/collections', EggCollectionController::class)
        ->names('eggs.collections')
        ->parameters(['collections' => 'collection']);

    Route::resource('eggs/sales', EggSaleController::class)
        ->only(['index', 'create', 'store', 'show', 'destroy'])
        ->names('eggs.sales')
        ->parameters(['sales' => 'sale']);

    Route::patch(
        '/eggs/sales/{sale}/paid',
        [EggSaleController::class, 'markPaid']
    )->name('eggs.sales.paid');

    // -----------------------------------------
    // COMPTABILITÉ (administrateurs uniquement)
    // -----------------------------------------

    Route::middleware('can:access-accounting')->group(function () {

        Route::get('/accounting', [AccountingController::class, 'index'])
            ->name('accounting.index');

        Route::resource('accounting/transactions', TransactionController::class)
            ->except(['index', 'show'])
            ->names('transactions')
            ->parameters(['transactions' => 'transaction']);

    });

});
