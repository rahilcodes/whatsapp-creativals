<?php
header('Content-Type: text/plain; charset=utf-8');
if (($_GET['secret'] ?? '') !== 'whatsapp_ai_secret_2026') { http_response_code(401); die('Unauthorized'); }

echo "=== Port 3000 ===\n";
$conn = @fsockopen('127.0.0.1', 3000, $errno, $errstr, 2);
echo is_resource($conn) ? "Port 3000 OPEN ✅\n" : "Port 3000 CLOSED ❌ ($errstr)\n";
if (is_resource($conn)) fclose($conn);

echo "\n=== Bot .env ===\n";
echo shell_exec('cat /var/www/whatsapp-ai/bot/.env 2>&1');

echo "\n=== Node processes ===\n";
echo shell_exec('ps aux | grep node | grep -v grep 2>&1') ?: "No node processes\n";

echo "\n=== PM2 list ===\n";
echo shell_exec('pm2 list 2>&1') ?: "pm2 not accessible\n";

echo "\n=== Laravel .env BOT_URL ===\n";
echo shell_exec('grep BOT_URL /var/www/whatsapp-ai/app/.env 2>&1');

echo "\n=== Laravel app up check ===\n";
$ch = curl_init("http://127.0.0.1/up");
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>3]);
$res = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "HTTP $code: " . ($res ?: curl_error($ch)) . "\n";
curl_close($ch);
