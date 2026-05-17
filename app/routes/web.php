<?php

use App\Http\Controllers\BusinessMemoryController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('admin.auth')->group(function () {
    // ── Main Dashboard ────────────────────────────────────────────
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // ── Chats ─────────────────────────────────────────────────────
    Route::prefix('chats')->name('chats.')->group(function () {
        Route::get('/',                   [ChatController::class, 'index'])->name('index');
        Route::get('/{phone}',            [ChatController::class, 'show'])->name('show');
        Route::post('/{phone}/takeover',  [ChatController::class, 'toggleTakeover'])->name('takeover');
        Route::delete('/{phone}/memory',  [ChatController::class, 'clearMemory'])->name('clear-memory');
    });

    // ── Settings ──────────────────────────────────────────────────
    Route::get('/settings',  [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // ── Business Memory CRUD ──────────────────────────────────────
    Route::prefix('business')->name('business.')->group(function () {
        Route::get('/',              [BusinessMemoryController::class, 'index'])->name('index');
        Route::post('/',             [BusinessMemoryController::class, 'store'])->name('store');
        Route::put('/{id}',         [BusinessMemoryController::class, 'update'])->name('update');
        Route::delete('/{id}',      [BusinessMemoryController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle', [BusinessMemoryController::class, 'toggleActive'])->name('toggle');
    });
});
