<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SupplierController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication middleware group
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/customer', [CustomerController::class, 'index'])->name('customer.index');
    Route::post('/customer', [CustomerController::class, 'store'])->name('customer.store');
    Route::put('/customer/{customer}', [CustomerController::class, 'update'])->name('customer.update');
    Route::delete('/customer/{customer}', [CustomerController::class, 'destroy'])->name('customer.destroy');

    Route::get('/supplier', [SupplierController::class, 'index'])->name('supplier.index');
    Route::post('/supplier', [SupplierController::class, 'store'])->name('supplier.store');
    Route::post('/supplier/{supplier}', [SupplierController::class, 'update'])->name('supplier.update');
    Route::delete('/supplier/{supplier}', [SupplierController::class, 'destroy'])->name('supplier.destroy');

    Route::get('/purchase', [PurchaseController::class, 'index'])->name('purchase.index');
    Route::post('/purchase', [PurchaseController::class, 'store'])->name('purchase.store');
    Route::post('/purchase/{form}', [PurchaseController::class, 'update'])->name('purchase.update');
    Route::delete('/purchase/{form}', [PurchaseController::class, 'destroy'])->name('purchase.destroy');

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    // Inventory routes
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');

    // Sales routes
    Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');

    // About route
    Route::get('/about', [AboutController::class, 'index'])->name('about.index');
});

// The welcome route is no longer needed since we're redirecting to login
// You can keep it if you want to use it for some other purpose
// Route::get('/welcome', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// })->name('welcome');
