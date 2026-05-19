<?php

use App\Http\Controllers\Api\BotStatusController;
use App\Http\Controllers\Api\BotStartupController;
use App\Http\Controllers\Api\QrController;
use App\Http\Controllers\Api\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

// ── WhatsApp Bot → Laravel (called from Node.js) ─────────────
Route::post('/whatsapp/message',  [WhatsAppWebhookController::class, 'receive']);
Route::post('/whatsapp/status',   [WhatsAppWebhookController::class, 'statusUpdate']);
Route::post('/qr',                [QrController::class, 'store']);

// ── Bot Startup Handshake (Node.js calls these on boot) ──────
Route::get('/tenants/active',     [BotStartupController::class, 'getActiveTenants']);
Route::post('/bot/startup-reset', [BotStartupController::class, 'startupReset']);

