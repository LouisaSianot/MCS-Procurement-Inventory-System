<?php


use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/inventory', fn() => view('dashboard.index'))->name('inventory.index');
    Route::get('/inventory/create', fn() => view('dashboard.index'))->name('inventory.create');
    Route::get('/inventory/{id}', fn($id) => view('dashboard.index', ['inventory_item_id' => $id]))->name('inventory.show');

    Route::get('/ge-orders', fn() => view('dashboard.index'))->name('ge-orders.index');
    Route::get('/ge-orders/create', fn() => view('dashboard.index'))->name('ge-orders.create');
    Route::get('/ge-orders/{id}', fn($id) => view('dashboard.index', ['ge_order_id' => $id]))->name('ge-orders.show');

    Route::get('/procurement', fn() => view('dashboard.index'))->name('procurement.index');
    Route::get('/procurement/create', fn() => view('dashboard.index'))->name('procurement.create');
    Route::get('/procurement/{id}', fn($id) => view('dashboard.index', ['purchase_id' => $id]))->name('procurement.show');

    Route::get('/receiving', fn() => view('dashboard.index'))->name('receiving.index');
    Route::get('/reports', fn() => view('dashboard.index'))->name('reports.index');
    Route::get('/reports/activity', fn() => view('dashboard.index'))->name('reports.activity');
});

require __DIR__ . '/auth.php';
