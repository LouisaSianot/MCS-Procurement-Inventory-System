<?php

use App\Http\Controllers\GEOrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| GE Orders Module Routes
|--------------------------------------------------------------------------
| Drop this file's contents into routes/web.php inside your existing
| `Route::middleware(['auth'])->group(function () { ... });` block,
| OR place this file at routes/ge_orders.php and add the include below
| to routes/web.php:
|
|   require __DIR__.'/ge_orders.php';
|
*/

Route::middleware(['auth'])->group(function () {
    // Standard resource (index, create, store, show, edit, update, destroy)
    Route::resource('ge-orders', GEOrderController::class);

    // Custom workflow actions — must be declared BEFORE the resource if you
    // prefer explicit ordering, but resource() does not capture these URIs so
    // placement after is safe.
    Route::post('ge-orders/{ge_order}/submit', [GEOrderController::class, 'submit'])
        ->name('ge-orders.submit');

    Route::post('ge-orders/{ge_order}/approve', [GEOrderController::class, 'approve'])
        ->name('ge-orders.approve');

    Route::post('ge-orders/{ge_order}/reject', [GEOrderController::class, 'reject'])
        ->name('ge-orders.reject');

    Route::post('ge-orders/{ge_order}/cancel', [GEOrderController::class, 'cancel'])
        ->name('ge-orders.cancel');
});
