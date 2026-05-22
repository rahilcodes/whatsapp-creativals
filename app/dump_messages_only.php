<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "MESSAGES FOR TENANT 15:\n";
$messages = App\Models\Message::orderBy('id', 'desc')->limit(20)->get();
foreach ($messages as $m) {
    echo "ID: " . $m->id . " | Phone: " . $m->phone . " | Role: " . $m->role . " | Content: " . substr($m->content, 0, 100) . " | Created At: " . $m->created_at . " | JID: " . $m->jid . "\n";
}
