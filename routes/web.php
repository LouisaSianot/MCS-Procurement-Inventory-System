<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportsController;
use App\Models\GEOrder;
use App\Models\ItemBranch;
use App\Models\PurchaseOrder;
use App\Http\Controllers\GeOrderController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('welcome');

Route::get('/dashboard', function () {
    $recentOrders = GEOrder::with('supplier')
        ->latest('order_date')
        ->latest('id')
        ->limit(5)
        ->get()
        ->map(fn(GEOrder $order) => (object) [
            'id' => $order->id,
            'number' => $order->order_number,
            'supplier' => $order->supplier?->name ?? 'N/A',
            'type' => strtoupper($order->inventory_flag ?? 'NON-STOCK'),
            'date' => $order->order_date?->format('d M Y') ?? '-',
            'amount' => 'K ' . number_format((float) ($order->total_amount ?? 0), 2),
            'status' => $order->status,
        ]);

    $totalOrders = GEOrder::count();
    $pendingOrders = GEOrder::where('status', GEOrder::STATUS_PENDING)->count();
    $approvedOrders = GEOrder::where('status', GEOrder::STATUS_APPROVED)->count();
    $awaitingReceipt = PurchaseOrder::whereIn('status', [PurchaseOrder::STATUS_ORDERED, PurchaseOrder::STATUS_BACKORDER, PurchaseOrder::STATUS_PARTIALLY_RECEIVED])->count();

    $inventoryRecords = ItemBranch::with(['item', 'branchRecord'])->get();
    $lowStockRecords = $inventoryRecords->filter(fn (ItemBranch $record) => in_array($record->stockStatus(), [ItemBranch::STATUS_LOW_STOCK, ItemBranch::STATUS_OUT_OF_STOCK], true));
    $lowStockItems = $lowStockRecords->count();
    $totalInventoryItems = $inventoryRecords->count();
    $stockNormal = $inventoryRecords->filter(fn (ItemBranch $record) => $record->stockStatus() === ItemBranch::STATUS_IN_STOCK)->count();
    $stockLow = $inventoryRecords->filter(fn (ItemBranch $record) => $record->stockStatus() === ItemBranch::STATUS_LOW_STOCK)->count();
    $stockOut = $inventoryRecords->filter(fn (ItemBranch $record) => $record->stockStatus() === ItemBranch::STATUS_OUT_OF_STOCK)->count();
    $lowStockTable = $lowStockRecords
        ->sortBy([
            [fn (ItemBranch $record) => $record->stockStatus() === ItemBranch::STATUS_OUT_OF_STOCK ? 0 : 1, 'asc'],
            [fn (ItemBranch $record) => (float) $record->reorder_level - (float) $record->current_stock, 'desc'],
        ])
        ->take(5)
        ->map(fn (ItemBranch $record) => (object) [
            'id' => $record->id,
            'name' => $record->item?->description ?? 'Item #' . $record->item_id,
            'current_stock' => $record->current_stock,
            'reorder_level' => $record->reorder_level,
            'shortfall' => max(0, (float) $record->reorder_level - (float) $record->current_stock),
            'status' => $record->stockStatus(),
            'location' => $record->location ?: ($record->branchRecord?->name ?? '—'),
        ]);

    return view('dashboard.index', compact('recentOrders', 'totalOrders', 'pendingOrders', 'approvedOrders', 'awaitingReceipt', 'lowStockItems', 'totalInventoryItems', 'stockNormal', 'stockLow', 'stockOut', 'lowStockTable'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/{itemBranch}', [InventoryController::class, 'show'])->name('inventory.show');

    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');

    //Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

require __DIR__ . '/auth.php';
require __DIR__ . '/ge_orders.php';
require __DIR__ . '/procurement.php';

require __DIR__ . "/admin.php";
