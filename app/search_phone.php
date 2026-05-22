<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "MESSAGES FOR 191555608510539:\n";
$msgs = App\Models\Message::where('phone', 'like', '%191555608510539%')->orWhere('jid', 'like', '%191555608510539%')->get();
foreach ($msgs as $m) {
    echo "ID: " . $m->id . " | Phone: " . $m->phone . " | Role: " . $m->role . " | Content: " . substr($m->content, 0, 100) . " | Created At: " . $m->created_at . "\n";
}

echo "\nACTIVITY LOGS FOR 191555608510539:\n";
$logs = App\Models\ActivityLog::where('phone', 'like', '%191555608510539%')->orWhere('description', 'like', '%191555608510539%')->get();
foreach ($logs as $l) {
    echo "ID: " . $l->id . " | Event: " . $l->event_type . " | Description: " . $l->description . " | Created At: " . $l->created_at . "\n";
}

echo "\nFLAGGED FOR 191555608510539:\n";
$flags = App\Models\FlaggedConversation::where('phone', 'like', '%191555608510539%')->get();
foreach ($flags as $f) {
    echo "ID: " . $f->id . " | Phone: " . $f->phone . " | Reason: " . $f->reason . " | Created At: " . $f->created_at . "\n";
}
