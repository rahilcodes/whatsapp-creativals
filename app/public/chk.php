<?php
header('Content-Type: text/plain; charset=utf-8');
if (($_GET['s'] ?? '') !== 'wai2026') { http_response_code(401); die('no'); }

$botDir  = '/var/www/whatsapp-ai/bot';
$logFile = $botDir . '/bot.log';

// ── Check if bot is already running ──────────────────────────
$conn = @fsockopen('127.0.0.1', 3000, $errno, $errstr, 2);
if (is_resource($conn)) {
    fclose($conn);
    echo "✅ Bot is ALREADY RUNNING on port 3000\n";
    $ch = curl_init("http://127.0.0.1:3000/health");
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>3]);
    echo "Health: " . curl_exec($ch) . "\n";
    curl_close($ch);
    echo "LARAVEL_URL: " . shell_exec("grep LARAVEL_URL $botDir/.env 2>&1");
    die();
}

echo "❌ Bot is DOWN. Starting it now...\n";

// ── Make sure LARAVEL_URL is correct ─────────────────────────
shell_exec("sed -i 's|LARAVEL_URL=.*|LARAVEL_URL=https://ichatup.com|' $botDir/.env 2>&1");
echo "LARAVEL_URL set to: " . shell_exec("grep LARAVEL_URL $botDir/.env 2>&1");

// ── Start bot in background ───────────────────────────────────
$cmd = "cd $botDir && nohup node src/index.js >> $logFile 2>&1 &";
shell_exec($cmd);

// ── Wait up to 10s for port 3000 to open ─────────────────────
echo "Waiting for bot to start";
for ($i = 0; $i < 10; $i++) {
    sleep(1);
    echo ".";
    $conn = @fsockopen('127.0.0.1', 3000, $errno, $errstr, 1);
    if (is_resource($conn)) {
        fclose($conn);
        echo "\n✅ Bot started successfully!\n";
        $ch = curl_init("http://127.0.0.1:3000/health");
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>3]);
        echo "Health: " . curl_exec($ch) . "\n";
        curl_close($ch);
        die();
    }
}

echo "\n⚠️ Bot didn't respond in 10s. Check bot.log:\n";
echo shell_exec("tail -30 $logFile 2>&1");
