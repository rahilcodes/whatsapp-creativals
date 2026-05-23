<?php
header('Content-Type: text/plain; charset=utf-8');
if (($_GET['secret'] ?? '') !== 'whatsapp_ai_secret_2026') { http_response_code(401); die('Unauthorized'); }

echo "=== Port 3000 ===\n";
$conn = @fsockopen('127.0.0.1', 3000, $errno, $errstr, 2);
echo is_resource($conn) ? "Port 3000 OPEN ✅\n" : "Port 3000 CLOSED ❌ ($errstr)\n";
if (is_resource($conn)) fclose($conn);

echo "\n=== Bot /health ===\n";
$ch = curl_init("http://127.0.0.1:3000/health");
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>3]);
$res = curl_exec($ch);
echo $res ? "✅ $res\n" : "❌ FAILED: ".curl_error($ch)."\n";
curl_close($ch);

echo "\n=== Bot .env ===\n";
echo shell_exec('cat /var/www/whatsapp-ai/bot/.env 2>&1');

echo "\n=== Node processes ===\n";
$procs = shell_exec('ps aux | grep node | grep -v grep 2>&1');
echo $procs ?: "❌ No node processes running\n";

echo "\n=== Bot /status Tenant 5 ===\n";
$ch = curl_init("http://127.0.0.1:3000/status");
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>3,
    CURLOPT_HTTPHEADER=>["X-Bot-Secret: whatsapp_ai_secret_2026","X-Tenant-ID: 5"]]);
$res = curl_exec($ch);
echo $res ?: "FAILED: ".curl_error($ch);
curl_close($ch);

echo "\n\n=== DB T5 status ===\n";
try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    $s = \Illuminate\Support\Facades\DB::table('whatsapp_status')->where('tenant_id', 5)->first();
    echo $s ? "status={$s->status} | session_state={$s->session_state} | qr=".($s->qr_code ? 'YES' : 'NO')."\n" : "No record\n";
} catch (\Throwable $e) { echo "DB error: ".$e->getMessage()."\n"; }
