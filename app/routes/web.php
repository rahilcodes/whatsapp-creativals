<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Legal and policy routes
Route::get('/terms-and-conditions', function () {
    return view('legal.terms');
})->name('terms');

Route::get('/privacy-policy', function () {
    return view('legal.privacy');
})->name('privacy');

Route::get('/refund-policy', function () {
    return view('legal.refund');
})->name('refunds');

Route::middleware(['auth', 'subscribed', 'verified'])->group(function () {
    // Billing Upgrade and Actions
    Route::get('/billing', [\App\Http\Controllers\BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/subscribe', [\App\Http\Controllers\BillingController::class, 'createSubscription'])->name('billing.subscribe');
    Route::post('/billing/support-addon', [\App\Http\Controllers\BillingController::class, 'purchaseSupportAddon'])->name('billing.support-addon');
    Route::post('/billing/verify', [\App\Http\Controllers\BillingController::class, 'verifyPayment'])->name('billing.verify');

    // Onboarding Setup Wizard Routes
    Route::get('/onboarding', [\App\Http\Controllers\OnboardingController::class, 'index'])->name('onboarding.index');
    Route::post('/onboarding', [\App\Http\Controllers\OnboardingController::class, 'store'])->name('onboarding.store');
    Route::post('/onboarding/skip', [\App\Http\Controllers\OnboardingController::class, 'skip'])->name('onboarding.skip');

    // QR + Bot-start accessible during onboarding (before onboarded flag is set)
    // The dashboard calls the same URLs — nothing changes there.
    Route::prefix('api')->group(function () {
        Route::get('/qr',             [\App\Http\Controllers\Api\QrController::class, 'show']);
        Route::get('/qr/stream',      [\App\Http\Controllers\Api\QrController::class, 'stream']);
        Route::post('/bot/start',     [\App\Http\Controllers\Api\BotStatusController::class, 'startEngine']);
    });

    // App routes protected by onboarding completion
    Route::middleware(['onboarded'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // App Routes
        Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
        
        Route::get('/leads', [\App\Http\Controllers\LeadController::class, 'index'])->name('leads.index');
        Route::get('/leads/{id}', [\App\Http\Controllers\LeadController::class, 'show'])->name('leads.show');

        Route::get('/chats', [\App\Http\Controllers\ChatController::class, 'index'])->name('chats.index');
        Route::get('/chats/{phone}', [\App\Http\Controllers\ChatController::class, 'show'])->name('chats.show');
        Route::post('/chats/{phone}/takeover', [\App\Http\Controllers\ChatController::class, 'toggleTakeover'])->name('chats.takeover');
        Route::delete('/chats/{phone}/memory', [\App\Http\Controllers\ChatController::class, 'clearMemory'])->name('chats.clear-memory');

        Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');

        Route::get('/business', [\App\Http\Controllers\BusinessMemoryController::class, 'index'])->name('business.index');
        Route::post('/business', [\App\Http\Controllers\BusinessMemoryController::class, 'store'])->name('business.store');
        Route::put('/business/{id}', [\App\Http\Controllers\BusinessMemoryController::class, 'update'])->name('business.update');
        Route::delete('/business/{id}', [\App\Http\Controllers\BusinessMemoryController::class, 'destroy'])->name('business.destroy');
        Route::post('/business/{id}/toggle', [\App\Http\Controllers\BusinessMemoryController::class, 'toggleActive'])->name('business.toggle');

        // Dashboard AJAX Polling Routes (session-auth protected)
        Route::prefix('api')->group(function () {
            Route::post('/qr/clear',         [\App\Http\Controllers\Api\QrController::class, 'clearSession']);
            Route::get('/bot-status',        [\App\Http\Controllers\Api\BotStatusController::class, 'index']);
            Route::post('/bot/toggle',       [\App\Http\Controllers\Api\BotStatusController::class, 'toggle']);
            Route::post('/bot/pause',        [\App\Http\Controllers\Api\BotStatusController::class, 'pause']);
            Route::post('/bot/resume',       [\App\Http\Controllers\Api\BotStatusController::class, 'resume']);
            Route::post('/bot/reconnect',    [\App\Http\Controllers\Api\BotStatusController::class, 'reconnect']);
            Route::get('/bot/health',        [\App\Http\Controllers\Api\BotStatusController::class, 'health']);
            Route::get('/activity',          [\App\Http\Controllers\Api\BotStatusController::class, 'activity']);
        });
    });
});

require __DIR__.'/auth.php';

// Google OAuth Routes
use App\Http\Controllers\Auth\GoogleAuthController;

Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');

// Guest Admin Login Routes
use App\Http\Controllers\Admin\AdminLoginController;

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/admin/login', [AdminLoginController::class, 'login']);
});

// Impersonation stop route (needs general auth check but no super admin restriction)
use App\Http\Controllers\Admin\AdminDashboardController;

Route::post('/admin/impersonate/stop', [AdminDashboardController::class, 'stopImpersonate'])
    ->name('admin.impersonate.stop')
    ->middleware('auth');

// Webhooks
Route::post('/webhooks/razorpay', [\App\Http\Controllers\Webhooks\RazorpayWebhookController::class, 'handle']);

// Super Admin Routes
use App\Http\Controllers\Admin\TenantController;

Route::middleware(['auth', 'super.admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard',     [AdminDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/stats',         [AdminDashboardController::class, 'stats'])->name('stats');
    Route::get('/tenants/list',  [AdminDashboardController::class, 'tenants'])->name('tenants.list');
    Route::post('/toggle-ai',    [AdminDashboardController::class, 'toggleAi'])->name('toggle-ai');
    Route::post('/pause-tenant', [AdminDashboardController::class, 'pauseTenant'])->name('pause-tenant');
    Route::get('/activity',      [AdminDashboardController::class, 'activity'])->name('activity');
    Route::get('/system-health', [AdminDashboardController::class, 'systemHealth'])->name('system-health');
    
    Route::post('/impersonate/{tenant}', [AdminDashboardController::class, 'impersonate'])->name('impersonate');
    Route::post('/reconnect-tenant',     [AdminDashboardController::class, 'reconnectTenant'])->name('reconnect-tenant');
    Route::post('/tenants/create',       [AdminDashboardController::class, 'storeTenant'])->name('tenants.store');
    
    // Redirect old tenants list to dashboard
    Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
});


