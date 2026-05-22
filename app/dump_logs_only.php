<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "RECENT ACTIVITY LOGS:\n";
$logs = App\Models\ActivityLog::orderBy('id', 'desc')->limit(40)->get();
foreach ($logs as $l) {
    echo "ID: " . $l->id . " | Event: " . $l->event_type . " | Phone: " . $l->phone . " | Text: " . $l->description . " | Created At: " . $l->created_at . "\n";
}
