<?php
header('Content-Type: text/plain; charset=utf-8');

if (($_GET['secret'] ?? '') !== 'whatsapp_ai_secret_2026') {
    http_response_code(401);
    die('Unauthorized');
}

echo "=== System Info ===\n";
echo "Whoami: " . shell_exec('whoami') . "\n";
echo "PWD: " . getcwd() . "\n";

echo "\n=== PM2 Status ===\n";
echo shell_exec('pm2 status 2>&1') ?: "No output or pm2 not found/executable\n";

echo "\n=== Listening Ports (3000 check) ===\n";
$connection = @fsockopen('127.0.0.1', 3000, $errno, $errstr, 2);
if (is_resource($connection)) {
    echo "Port 3000 is OPEN\n";
    fclose($connection);
} else {
    echo "Port 3000 is CLOSED (Error: $errstr ($errno))\n";
}

echo "\n=== Test curl to Bot /health ===\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:3000/health");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
$res = curl_exec($ch);
if ($res === false) {
    echo "Curl to Bot /health failed: " . curl_error($ch) . "\n";
} else {
    echo "Curl to Bot /health response: " . $res . "\n";
}
curl_close($ch);

echo "\n=== Test curl to Bot /status (Tenant 1) ===\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://127.0.0.1:3000/status");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-Bot-Secret: whatsapp_ai_secret_2026',
    'X-Tenant-ID: 1'
]);
$res = curl_exec($ch);
if ($res === false) {
    echo "Curl to Bot /status failed: " . curl_error($ch) . "\n";
} else {
    echo "Curl to Bot /status response: " . $res . "\n";
}
curl_close($ch);

echo "\n=== Laravel Log (Last 50 lines) ===\n";
$logPath = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logPath)) {
    $lines = file($logPath);
    $lastLines = array_slice($lines, -50);
    echo implode('', $lastLines);
} else {
    echo "laravel.log not found at: $logPath\n";
}

echo "\n=== Bot Log (Last 50 lines) ===\n";
$botLogPath = __DIR__ . '/../../bot/bot.log';
if (file_exists($botLogPath)) {
    $lines = file($botLogPath);
    $lastLines = array_slice($lines, -50);
    echo implode('', $lastLines);
} else {
    echo "bot.log not found at: $botLogPath\n";
}

echo "\n=== Bot Directory structure ===\n";
echo shell_exec('ls -la ../../bot 2>&1');
