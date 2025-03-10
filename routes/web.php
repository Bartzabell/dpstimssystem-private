<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');

Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');

Route::get('/purchase', [PurchaseController::class, 'index'])->name('purchase.index');

Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');

Route::get('/about', [AboutController::class, 'index'])->name('about.index');
