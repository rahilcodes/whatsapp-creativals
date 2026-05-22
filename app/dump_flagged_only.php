<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "FLAGGED CONVERSATIONS:\n";
foreach (App\Models\FlaggedConversation::all() as $fc) {
    echo "Phone: " . $fc->phone . " | Reason: " . $fc->reason . " | Text: " . $fc->trigger_message . " | Created At: " . $fc->created_at . "\n";
}
