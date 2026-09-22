<?php

use App\Http\Controllers\AdjustmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:manage-inventory'])->group(function (): void {
    Route::resource('adjustments', AdjustmentController::class)->only(['index', 'create', 'store']);
});
