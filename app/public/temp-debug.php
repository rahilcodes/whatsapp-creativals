<?php
header('Content-Type: text/plain; charset=utf-8');
if (($_GET['secret'] ?? '') !== 'whatsapp_ai_secret_2026') { http_response_code(401); die('Unauthorized'); }

echo "=== DB whatsapp_status ===\n";
try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $rows = \Illuminate\Support\Facades\DB::table('whatsapp_status')->get();
    foreach ($rows as $r) {
        echo "T{$r->tenant_id}: status={$r->status} | session_state={$r->session_state} | qr=" . ($r->qr_code ? 'YES('.strlen($r->qr_code).'bytes)' : 'NO') . "\n";
    }
} catch (\Throwable $e) { echo "DB error: ".$e->getMessage()."\n"; }

echo "\n=== Bot /status all tenants ===\n";
for ($t = 1; $t <= 9; $t++) {
    $ch = curl_init("http://127.0.0.1:3000/status");
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>2,
        CURLOPT_HTTPHEADER=>["X-Bot-Secret: whatsapp_ai_secret_2026","X-Tenant-ID: $t"]]);
    $res = curl_exec($ch);
    echo "T$t: ".($res ?: "FAILED: ".curl_error($ch))."\n";
    curl_close($ch);
}

echo "\n=== PM2 list (via HOME=/root) ===\n";
putenv('HOME=/root');
echo shell_exec('HOME=/root pm2 list 2>&1') ?: "no output\n";

echo "\n=== Bot dir: auth_session folders ===\n";
echo shell_exec('ls /var/www/whatsapp-ai/bot/ 2>&1');
