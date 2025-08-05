<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\AuditLogController;

// Inventory resource routes (CRUD)
Route::resource('inventory', InventoryController::class);

// Audit log index route
Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit.logs');
