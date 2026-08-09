<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('inventory/{location}/preview', [InventoryController::class, 'preview'])->name('inventory.preview');
    Route::post('inventory/transfer', [InventoryController::class, 'transfer'])->name('inventory.transfer');
    Route::get('inventory/history', [AuditController::class, 'index'])->name('inventory.history');
});

require __DIR__.'/settings.php';
