<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryTransferController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\GeOrderController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('welcome');

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::redirect('/assets', '/inventory?category=Asset')->name('assets.index');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/transfers/create', [InventoryTransferController::class, 'create'])->name('inventory.transfers.create');
    Route::post('/inventory/transfers', [InventoryTransferController::class, 'store'])->name('inventory.transfers.store');
    Route::get('/inventory/{itemBranch}', [InventoryController::class, 'show'])->whereNumber('itemBranch')->name('inventory.show');

    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/{format}', [ReportsController::class, 'export'])->whereIn('format', ['xlsx', 'pdf'])->name('reports.export');
    Route::get('/exports/{exportRequest}/download', [ExportController::class, 'download'])->name('exports.download');
});

require __DIR__ . '/auth.php';
require __DIR__ . '/ge_orders.php';
require __DIR__ . '/procurement.php';

require __DIR__ . "/admin.php";
