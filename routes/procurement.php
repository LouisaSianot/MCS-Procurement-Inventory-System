<?php

use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseReceiptController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::resource('receiving', PurchaseReceiptController::class)->only(['index', 'create', 'store', 'show']);

    Route::resource('procurement', PurchaseOrderController::class)->parameters([
        'procurement' => 'procurement',
    ]);
});
