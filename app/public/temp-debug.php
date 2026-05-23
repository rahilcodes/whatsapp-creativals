<?php
header('Content-Type: text/plain; charset=utf-8');

if (($_GET['secret'] ?? '') !== 'whatsapp_ai_secret_2026') {
    http_response_code(401);
    die('Unauthorized');
}

echo "=== Laravel Database whatsapp_status Table ===\n";
try {
    // Bootstrap Laravel to query DB
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    $statuses = \Illuminate\Support\Facades\DB::table('whatsapp_status')->get();
    foreach ($statuses as $s) {
        echo "Tenant {$s->tenant_id}: Status={$s->status}, SessionState={$s->session_state}, HasQR=" . ($s->qr_code ? 'YES' : 'NO') . "\n";
    }
} catch (\Throwable $e) {
    echo "DB Query failed: " . $e->getMessage() . "\n";
}

echo "\n=== Bot Status for Tenants 1 to 9 ===\n";
for ($t = 1; $t <= 9; $t++) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:3000/status");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-Bot-Secret: whatsapp_ai_secret_2026',
        'X-Tenant-ID: ' . $t
    ]);
    $res = curl_exec($ch);
    if ($res === false) {
        echo "Tenant {$t}: Bot /status call failed: " . curl_error($ch) . "\n";
    } else {
        echo "Tenant {$t}: Bot /status response: " . $res . "\n";
    }
    curl_close($ch);
}

echo "\n=== Bot Health Detail for Tenants 1 to 9 ===\n";
for ($t = 1; $t <= 9; $t++) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:3000/health/detail");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 2);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'X-Bot-Secret: whatsapp_ai_secret_2026',
        'X-Tenant-ID: ' . $t
    ]);
    $res = curl_exec($ch);
    if ($res === false) {
        echo "Tenant {$t}: Bot /health/detail call failed: " . curl_error($ch) . "\n";
    } else {
        echo "Tenant {$t}: Bot /health/detail response: " . $res . "\n";
    }
    curl_close($ch);
}
