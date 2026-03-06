<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\TechnicianController;
use App\Http\Controllers\SparePartController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ReportController;

// Giriş gerektiren rotalar
Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Müşteriler
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/',               [CustomerController::class, 'index'])->name('index');
        Route::get('/create',         [CustomerController::class, 'create'])->name('create');
        Route::post('/',              [CustomerController::class, 'store'])->name('store');
        Route::get('/{customer}',     [CustomerController::class, 'show'])->name('show');
        Route::get('/{customer}/edit',[CustomerController::class, 'edit'])->name('edit');
        Route::put('/{customer}',     [CustomerController::class, 'update'])->name('update');
        Route::delete('/{customer}',  [CustomerController::class, 'destroy'])->name('destroy');
        Route::get('/api/search',     [CustomerController::class, 'search'])->name('search');
    });

    // Servis Talepleri
    Route::prefix('service')->name('service.')->group(function () {
        Route::get('/',                      [ServiceRequestController::class, 'index'])->name('index');
        Route::get('/create',                [ServiceRequestController::class, 'create'])->name('create');
        Route::post('/',                     [ServiceRequestController::class, 'store'])->name('store');
        Route::get('/{service}',             [ServiceRequestController::class, 'show'])->name('show');
        Route::get('/{service}/edit',        [ServiceRequestController::class, 'edit'])->name('edit');
        Route::put('/{service}',             [ServiceRequestController::class, 'update'])->name('update');
        Route::delete('/{service}',          [ServiceRequestController::class, 'destroy'])->name('destroy');
        Route::post('/{service}/status',     [ServiceRequestController::class, 'updateStatus'])->name('status');
        Route::post('/{service}/note',       [ServiceRequestController::class, 'addNote'])->name('note');
    });

    // Teknisyenler
    Route::prefix('technicians')->name('technicians.')->group(function () {
        Route::get('/',                [TechnicianController::class, 'index'])->name('index');
        Route::post('/',               [TechnicianController::class, 'store'])->name('store');
        Route::put('/{technician}',    [TechnicianController::class, 'update'])->name('update');
        Route::delete('/{technician}', [TechnicianController::class, 'destroy'])->name('destroy');
    });

    // Yedek Parça & Stok
    Route::prefix('spare-parts')->name('spare-parts.')->group(function () {
        Route::get('/',                   [SparePartController::class, 'index'])->name('index');
        Route::post('/',                  [SparePartController::class, 'store'])->name('store');
        Route::put('/{sparePart}',        [SparePartController::class, 'update'])->name('update');
        Route::delete('/{sparePart}',     [SparePartController::class, 'destroy'])->name('destroy');
        Route::post('/{sparePart}/stock', [SparePartController::class, 'addStock'])->name('stock');
        Route::post('/use',               [SparePartController::class, 'usePart'])->name('use');
    });

    // Faturalar
    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::get('/',                    [InvoiceController::class, 'index'])->name('index');
        Route::get('/create',              [InvoiceController::class, 'create'])->name('create');
        Route::post('/',                   [InvoiceController::class, 'store'])->name('store');
        Route::get('/{invoice}',           [InvoiceController::class, 'show'])->name('show');
        Route::delete('/{invoice}',        [InvoiceController::class, 'destroy'])->name('destroy');
        Route::post('/{invoice}/payment',  [InvoiceController::class, 'addPayment'])->name('payment');
    });

    // Raporlar
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/',          [ReportController::class, 'index'])->name('index');
        Route::get('/services',  [ReportController::class, 'services'])->name('services');
        Route::get('/financial', [ReportController::class, 'financial'])->name('financial');
        Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
    });
});

// Auth rotaları
require __DIR__ . '/auth.php';
