<?php
header('Content-Type: text/plain; charset=utf-8');
if (($_GET['secret'] ?? '') !== 'whatsapp_ai_secret_2026') { http_response_code(401); die('Unauthorized'); }

echo "=== Port 3000 check ===\n";
$conn = @fsockopen('127.0.0.1', 3000, $errno, $errstr, 2);
echo is_resource($conn) ? "Port 3000 OPEN ✅\n" : "Port 3000 CLOSED ❌ ($errstr)\n";
if (is_resource($conn)) fclose($conn);

echo "\n=== Bot /health check ===\n";
$ch = curl_init("http://127.0.0.1:3000/health");
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>3]);
$res = curl_exec($ch);
echo $res ? "Response: $res\n" : "FAILED: ".curl_error($ch)."\n";
curl_close($ch);

echo "\n=== sudo pm2 list ===\n";
echo shell_exec('sudo pm2 list 2>&1') ?: "no output\n";

echo "\n=== sudo pm2 status ichatup-bot ===\n";
echo shell_exec('sudo pm2 show ichatup-bot 2>&1') ?: "no output\n";

echo "\n=== Whoami / id ===\n";
echo shell_exec('whoami 2>&1; id 2>&1');

echo "\n=== Bot log (last 30 lines) ===\n";
echo shell_exec('sudo pm2 logs ichatup-bot --lines 30 --nostream 2>&1') ?: "no output\n";
