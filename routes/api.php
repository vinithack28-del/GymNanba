<?php

use App\Http\Controllers\Tenant\PosController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'tenant_user'])->prefix('v1/pos')->group(function (): void {
    Route::get('/products', [PosController::class, 'apiProducts']);
    Route::post('/products', [PosController::class, 'apiStoreProduct']);
    Route::put('/products/{product}', [PosController::class, 'apiUpdateProduct']);

    Route::get('/sales', [PosController::class, 'apiSales']);
    Route::post('/sales', [PosController::class, 'apiCheckout']);

    Route::get('/stock', [PosController::class, 'apiStock']);
    Route::post('/stock/restock', [PosController::class, 'apiRestock']);
    Route::post('/stock/adjust', [PosController::class, 'apiAdjust']);
});
