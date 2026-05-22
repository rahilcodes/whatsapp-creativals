<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$msg = \App\Models\Message::withoutGlobalScope('tenant')->latest()->first();
if ($msg) {
    echo "Message ID: {$msg->id}\n";
    echo "Message created_at (Carbon): " . $msg->created_at->toIso8601String() . "\n";
    echo "now() (Carbon): " . now()->toIso8601String() . "\n";
    echo "diffInSeconds: " . $msg->created_at->diffInSeconds(now()) . "\n";
    
    // Test absolute difference vs directional difference
    // diffInSeconds is absolute by default, but let's check:
    echo "msg_time timestamp: " . $msg->created_at->timestamp . "\n";
    echo "now timestamp: " . now()->timestamp . "\n";
} else {
    echo "No messages found.\n";
}
