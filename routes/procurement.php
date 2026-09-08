<?php

use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseReceiptController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('procurement/export/{format}', [PurchaseOrderController::class, 'export'])->whereIn('format', ['xlsx', 'pdf'])->name('procurement.export');
    Route::get('receiving/export/{format}', [PurchaseReceiptController::class, 'export'])->whereIn('format', ['xlsx', 'pdf'])->name('receiving.export');

    Route::resource('receiving', PurchaseReceiptController::class)->only(['index', 'create', 'store', 'show']);

    Route::resource('procurement', PurchaseOrderController::class)->parameters([
        'procurement' => 'procurement',
    ]);
});
