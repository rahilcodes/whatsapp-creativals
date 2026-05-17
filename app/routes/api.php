<?php

use App\Http\Controllers\Api\BotStatusController;
use App\Http\Controllers\Api\QrController;
use App\Http\Controllers\Api\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

// ── WhatsApp Bot → Laravel (called from Node.js) ─────────────
Route::post('/whatsapp/message', [WhatsAppWebhookController::class, 'receive']);
Route::post('/whatsapp/status',  [WhatsAppWebhookController::class, 'statusUpdate']);
Route::post('/qr',               [QrController::class, 'store']);

// ── Dashboard → Laravel (AJAX polling) ───────────────────────
Route::get('/qr',                [QrController::class, 'show']);
Route::get('/bot-status',        [BotStatusController::class, 'index']);
Route::post('/bot/toggle',       [BotStatusController::class, 'toggle']);
Route::post('/bot/pause',        [BotStatusController::class, 'pause']);
Route::post('/bot/resume',       [BotStatusController::class, 'resume']);
Route::post('/bot/reconnect',    [BotStatusController::class, 'reconnect']);
Route::post('/bot/start',        [BotStatusController::class, 'startEngine']);
Route::get('/activity',          [BotStatusController::class, 'activity']);
