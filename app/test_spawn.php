<?php
$botDir = realpath(__DIR__ . '/../bot');
$botEsc = str_replace('/', '\\', $botDir);
$logFile = $botEsc . '\\debug.log';

echo "Bot dir: $botDir\n";

$vbs  = "Set ws = CreateObject(\"WScript.Shell\")\r\n";
$vbs .= "ws.CurrentDirectory = \"{$botEsc}\"\r\n";
$vbs .= "ws.Run \"cmd.exe /c node src\\index.js > debug.log 2>&1\", 0, False\r\n";

$vbsPath = sys_get_temp_dir() . '\\start_wa_bot_test.vbs';
file_put_contents($vbsPath, $vbs);

exec("wscript.exe \"{$vbsPath}\"", $out, $code);
echo "WScript exit code: $code\n";

echo "Waiting 5 seconds...\n";
sleep(5);

if (file_exists($logFile)) {
    echo "=== LOG OUTPUT ===\n";
    echo file_get_contents($logFile);
}
