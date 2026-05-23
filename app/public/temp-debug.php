<?php
header('Content-Type: text/plain; charset=utf-8');
if (($_GET['secret'] ?? '') !== 'whatsapp_ai_secret_2026') { http_response_code(401); die('Unauthorized'); }

echo "=== User context ===\n";
echo shell_exec('whoami; id');

echo "\n=== Port 3000 ===\n";
$conn = @fsockopen('127.0.0.1', 3000, $errno, $errstr, 2);
echo is_resource($conn) ? "Port 3000 OPEN ✅\n" : "Port 3000 CLOSED ❌ ($errstr)\n";
if (is_resource($conn)) fclose($conn);

echo "\n=== Bot /health ===\n";
$ch = curl_init("http://127.0.0.1:3000/health");
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>3]);
$res = curl_exec($ch);
echo $res ?: "FAILED: ".curl_error($ch);
curl_close($ch);

echo "\n\n=== Check node processes ===\n";
echo shell_exec('ps aux | grep node | grep -v grep 2>&1') ?: "No node processes found\n";

echo "\n=== PM2 processes (no sudo) ===\n";
echo shell_exec('pm2 list 2>&1') ?: "no output\n";

echo "\n=== pm2.config.js exists? ===\n";
echo shell_exec('ls -la /var/www/whatsapp-ai/bot/pm2.config.js 2>&1');
echo shell_exec('head -3 /var/www/whatsapp-ai/bot/pm2.config.js 2>&1');

echo "\n=== .env BOT_URL on Laravel side ===\n";
echo shell_exec('grep BOT_URL /var/www/whatsapp-ai/app/.env 2>&1');
echo shell_exec('grep LARAVEL_URL /var/www/whatsapp-ai/bot/.env 2>&1');
