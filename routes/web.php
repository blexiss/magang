<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\AuditLogController;

// Inventory resource routes (CRUD)
Route::resource('inventory', InventoryController::class);
Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');


// Additional API routes for AJAX JSON update/delete (for offline sync)
Route::put('/inventory/{id}', [InventoryController::class, 'apiUpdate'])->name('inventory.apiUpdate');
Route::delete('/inventory/{id}', [InventoryController::class, 'apiDelete'])->name('inventory.apiDelete');

// Audit log index route
Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit.logs');
