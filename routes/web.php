<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

Route::prefix('api/sales')->group(function () {
    Route::get('/monthly-income/{year}', [ChartController::class, 'getMonthlyIncome']);
    Route::get('/available-years', [ChartController::class, 'getAvailableYears']);
    Route::get('/monthly-sales-quantity/{year}', [ChartController::class, 'getMonthlySalesQuantity']);
    Route::get('/todays-sales-by-item', [ChartController::class, 'getTodaysSalesByItem']);
    Route::get('/weekly-sales-by-item', [ChartController::class, 'getWeeklySalesByItem']);
});

// Authentication middleware group
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/customer', [CustomerController::class, 'index'])->name('customer.index');
    Route::post('/customer', [CustomerController::class, 'store'])->name('customer.store');
    Route::put('/customer/{customer}', [CustomerController::class, 'update'])->name('customer.update');
    Route::delete('/customer/{customer}', [CustomerController::class, 'destroy'])->name('customer.destroy');

    Route::get('/supplier', [SupplierController::class, 'index'])->name('supplier.index');
    Route::post('/supplier', [SupplierController::class, 'store'])->name('supplier.store');
    Route::put('/supplier/{supplier}', [SupplierController::class, 'update'])->name('supplier.update');
    Route::delete('/supplier/{supplier}', [SupplierController::class, 'destroy'])->name('supplier.destroy');

    Route::get('/purchase', [PurchaseController::class, 'index'])->name('purchase.index');
    Route::post('/purchase', [PurchaseController::class, 'store'])->name('purchase.store');
    Route::post('/purchase/{form}', [PurchaseController::class, 'update'])->name('purchase.update');
    Route::delete('/purchase/{form}', [PurchaseController::class, 'destroy'])->name('purchase.destroy');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::post('/inventory/{inventory}', [InventoryController::class, 'update'])->name('inventory.update');
    Route::delete('/inventory/{inventory}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

    Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
    Route::post('/sales', [SalesController::class, 'store'])->name('sales.store');
    Route::post('/sales/{form}', [SalesController::class, 'update'])->name('sales.update');
    Route::delete('/sales/{form}', [SalesController::class, 'destroy'])->name('sales.destroy');
    Route::get('/sales/{form}', [SalesController::class, 'show'])->name('sales.show');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/category', [SettingsController::class, 'categoryStore'])->name('category.store');
    Route::put('/category/{category}', [SettingsController::class, 'categoryUpdate'])->name('category.update');
    Route::delete('/category/{category}', [SettingsController::class, 'categoryDestroy'])->name('category.destroy');
    Route::post('/color', [SettingsController::class, 'colorStore'])->name('color.store');
    Route::put('/color/{color}', [SettingsController::class, 'colorUpdate'])->name('color.update');
    Route::delete('/color/{color}', [SettingsController::class, 'colorDestroy'])->name('color.destroy');
    Route::post('/material', [SettingsController::class, 'materialStore'])->name('material.store');
    Route::put('/material/{material}', [SettingsController::class, 'materialUpdate'])->name('material.update');
    Route::delete('/material/{material}', [SettingsController::class, 'materialDestroy'])->name('material.destroy');
    Route::post('/uom', [SettingsController::class, 'uomStore'])->name('uom.store');
    Route::put('/uom/{uom}', [SettingsController::class, 'uomUpdate'])->name('uom.update');
    Route::delete('/uom/{uom}', [SettingsController::class, 'uomDestroy'])->name('uom.destroy');
    Route::post('/discount', [SettingsController::class, 'discountStore'])->name('discount.store');
    Route::put('/discount/{discount}', [SettingsController::class, 'discountUpdate'])->name('discount.update');
    Route::delete('/discount/{discount}', [SettingsController::class, 'discountDestroy'])->name('discount.destroy');

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    Route::get('/user', [UserController::class, 'index'])->name('user.index');
    Route::post('/user', [UserController::class, 'store'])->name('user.store');
    Route::put('/user/{user}', [UserController::class, 'update'])->name('user.update');
    Route::delete('/user/{user}', [UserController::class, 'destroy'])->name('user.destroy');

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
