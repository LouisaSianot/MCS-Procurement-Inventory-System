<?php


use App\Http\Controllers\ProfileController;
use App\Models\GEOrder;
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

    $totalOrders = GEOrder::whereMonth('order_date', now()->month)
        ->whereYear('order_date', now()->year)
        ->count();

    $pendingApprovals = GEOrder::where('approval_status', GEOrder::APPROVAL_PENDING_APPROVAL)->count();

    return view('dashboard.index', compact('recentOrders', 'totalOrders', 'pendingApprovals'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/inventory', fn() => view('dashboard.index'))->name('inventory.index');
    Route::get('/inventory/create', fn() => view('dashboard.index'))->name('inventory.create');
    Route::get('/inventory/{id}', fn($id) => view('dashboard.index', ['inventory_item_id' => $id]))->name('inventory.show');

    Route::get('/procurement', fn() => view('dashboard.index'))->name('procurement.index');
    Route::get('/procurement/create', fn() => view('dashboard.index'))->name('procurement.create');
    Route::get('/procurement/{id}', fn($id) => view('dashboard.index', ['purchase_id' => $id]))->name('procurement.show');

    Route::get('/receiving', fn() => view('dashboard.index'))->name('receiving.index');
    Route::get('/reports', fn() => view('dashboard.index'))->name('reports.index');
    Route::get('/reports/activity', fn() => view('dashboard.index'))->name('reports.activity');

    //Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

require __DIR__ . '/auth.php';
require __DIR__ . '/ge_orders.php';
