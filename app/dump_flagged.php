<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "FLAGGED CONVERSATIONS:\n";
print_r(App\Models\FlaggedConversation::all()->toArray());

echo "\nBOT SETTINGS:\n";
print_r(App\Models\BotSetting::all()->toArray());
