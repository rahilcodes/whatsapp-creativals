<?php
$raw = file_get_contents(__DIR__ . '/resources/views/dashboard/index.blade.php');
$lines = explode("\n", $raw);

$toCheck = [125, 132, 135, 173, 183, 217, 317, 318, 319, 356, 579]; // 0-indexed
foreach ($toCheck as $i) {
    if (isset($lines[$i])) {
        $line = $lines[$i];
        echo "Line " . ($i+1) . " hex: " . bin2hex($line) . "\n";
        echo "Line " . ($i+1) . " text: " . trim($line) . "\n\n";
    }
}
