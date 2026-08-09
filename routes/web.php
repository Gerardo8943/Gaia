<?php

use App\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('inventory/transfer', [InventoryController::class, 'transfer'])->name('inventory.transfer');
    Route::post('inventory/{location}/consume', [InventoryController::class, 'consume'])->name('inventory.consume');
});

require __DIR__.'/settings.php';
