<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$msg = App\Models\Message::where('phone', '19872931151964')->orderBy('id', 'desc')->first();
if ($msg) {
    echo "Phone: " . $msg->phone . "\nRole: " . $msg->role . "\nContent: " . $msg->content . "\nCreated At: " . $msg->created_at . "\n";
} else {
    echo "No message found.\n";
}
