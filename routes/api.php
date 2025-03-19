<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChartController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('api/sales')->group(function () {
    Route::get('/monthly-income/{year}', [ChartController::class, 'getMonthlyIncome']);
    Route::get('/available-years', [ChartController::class, 'getAvailableYears']);
});
