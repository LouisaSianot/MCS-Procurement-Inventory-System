<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\GeOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('ge-orders', GeOrderController::class);
});

Route::middleware(['auth', 'role:Administrator'])->group(function () {
    Route::get('/admin-test', function() {
        return 'You are an Administrator';
    });
});

require __DIR__.'/auth.php';
