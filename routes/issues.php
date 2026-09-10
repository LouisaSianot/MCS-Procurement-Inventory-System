<?php

use App\Http\Controllers\IssueController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:manage-inventory'])->group(function (): void {
    Route::resource('issues', IssueController::class)->only(['index', 'create', 'store']);
});
