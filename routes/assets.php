<?php

use App\Http\Controllers\AssetController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:manage-inventory'])->group(function (): void {
    Route::resource('assets', AssetController::class)->only(['index', 'create', 'store']);
});
