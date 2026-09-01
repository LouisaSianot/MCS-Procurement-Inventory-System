<?php

use App\Http\Controllers\PurchaseOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::resource('procurement', PurchaseOrderController::class)->parameters([
        'procurement' => 'procurement',
    ]);
});
